# Page creation and slug concurrency

Task `7992fb6c`, local implementation 2026-09-18; pending human Verification.

## Reproduced failure

Four simultaneous real `Document::create` / `ContentApiService::createPage`
calls in a disposable MariaDB schema produced two copies of `concurrent-page`
and two copies of `1-concurrent-page`. A test query hook delayed availability
checks to expose the race. Admin ordering also happened after insertion without
a transaction, so an ordering failure could leave a partially completed create.

## Change

`PageWrites::run` obtains one database-scoped MariaDB/MySQL named lock before
starting a transaction. Admin create/save and API create/update participate in
the same lock. Slug checks therefore see the prior writer's committed result.
The lock retries short waits against a ten-second monotonic deadline, fails visibly if busy and releases in a
`finally` block on success or failure. Page writers must own their transaction;
nested use fails explicitly instead of silently weakening the boundary.

Creation performs slug allocation, insertion and ordering in one transaction.
Ordering retains the admin's new-page-first behavior, using `sorted, id` as a
deterministic order and positions 3, 5, 7, … . API creates now use that same
ordering rather than leaving a new row at the default zero. Relative order of
existing rows is preserved. API writes still create drafts and cannot set
publication, owner or sorting fields through the payload.

Generated collisions use the first available `1-`, `2-`, … prefix, truncating
the base so the slug stays within 250 characters. The exact request receiving
each slug depends on lock acquisition order; the resulting allocation is unique.
Admin slug edits allocate inside save, using the selected language column and
excluding the current row. API explicitly supplied duplicate slugs retain their
validation error. Different locale columns keep their existing separate URL
namespaces; no multilingual storage redesign was made.

## Schema and diagnostic boundary

```sh
php console.php pages:audit-slugs
```

This read-only command reports duplicate nonempty slug groups per existing
language column, using the actual database collation. Local result: `slug: 0`;
the local schema has no translated slug columns. Localized behavior is covered
with isolated test columns. Production content and schema were not inspected.

No unique index, SQL delta or duplicate-content repair was introduced. The
coordination covers the first-party admin/API writers found in the inventory;
direct SQL, external importers and old deployed workers do not participate.
Drain old writers when deploying and require future writers to use the shared
boundary. A future unique index needs production duplicate/null/locale review
and a separate compatible schema delta. Rebuild Nette DI/RobotLoader caches
for the new class and console registration. Hosting must support GET_LOCK.

## Verification

```sh
docker compose exec -T app php tests/manual/page-concurrency.php
docker compose exec -T app composer test
```

The MariaDB script creates a random test schema with the local pages table's
structure, launches four independent PHP processes, and removes the schema and
temporary files in `finally`. It checks the exact set of four distinct slugs,
stable positions and draft state. An SQL trigger then forces ordering failure
after insertion for both callers: rows/positions remain unchanged, the named
lock is free, and retry succeeds. It never writes into the regular pages table.
Three consecutive final concurrency runs passed. An earlier repeated run exposed
premature failure with a wall-clock deadline; the final deadline uses `hrtime`
and bounded retry instead. A genuinely busy/unavailable lock still fails closed.
The `reproduce` mode is a historical pre-fix check and is expected to fail its
duplicate assertion once the fix is installed.

`PageWritesTest.phpt` additionally checks stale admin edit objects targeting one
slug, separate translated columns, explicit API conflict validation, length
limits, draft state and SQL rollback. The full suite now has eleven passing
files on PHP 7.4.33. Visual admin save feedback remains human Verification.
Changes are local, uncommitted and undeployed.
