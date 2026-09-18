# Production deployment and data changes

CalorisCMS runs on Blueboard shared hosting. Production code is deployed by pushing a reviewed commit to the Blueboard `production` branch. Database and content changes are applied through Blueboard's authenticated Adminer/phpMyAdmin interface because the production database only accepts local hosting connections.

Do not add temporary migration, import, cache-clear, or password-reset scripts under `www/`. A secret URL does not provide an adequate audit trail or lifecycle, and forgotten helpers become a public attack surface.

## Responsibilities

- Git is the source of truth for application code, templates, assets, the schema dump, and versioned SQL migrations.
- Taskino is the source of truth for the owner, approval, execution time, migration filename and checksum, and verification result.
- Blueboard database backups are the recovery point for production data.
- `app/model/db_mysql.sql` remains the fresh-install baseline. Every accepted schema migration must also be reflected there.

## Prepare a data change

1. Create one narrowly scoped SQL file in `app/model/migrations/` using the naming convention `YYYY_MM_DD_short_description.sql`.
2. Include comments describing the purpose, required application version, precondition query, verification query, and rollback approach.
3. Prefer additive and idempotent SQL. Content updates must target stable ids or slugs and must include a precondition query that proves the intended rows are selected.
4. Test the SQL against a disposable local database restored from the current production-compatible schema or backup.
5. Update `app/model/db_mysql.sql` when the migration changes schema or seed settings.
6. Record the filename and SHA-256 checksum on the Taskino task before production execution.

The existing migration files predate this header convention. They are retained as historical deltas and must not be edited after production use; create a new corrective migration instead.

## Production sequence

1. Confirm the deployment commit, working tree, and Taskino task.
2. Create or confirm a current production database backup.
3. Run the migration's precondition query in Blueboard Adminer/phpMyAdmin and save the row counts in Taskino.
4. For backward-compatible additive changes, apply the SQL before deploying code. For changes that require new code first, deploy compatible code before applying SQL. Document the chosen order on the task.
5. Apply the exact reviewed SQL file through the authenticated database interface. Do not copy an edited ad-hoc variant into production.
6. Run the migration's verification query and record the result.
7. Push the reviewed commit to `blueboard/production`. Blueboard runs the supported `.deploy` directives: Composer installs locked production dependencies and the Nette cache directory is removed so it can be rebuilt under `www/temp`.
8. Smoke-test `/`, `/admin`, the affected feature, and any changed API/feed route.
9. Keep the task In Progress until the user accepts the combined result.

If any precondition is unexpected, stop before mutation. If a deployment or migration fails, preserve the exact error, do not rerun blindly, and restore from the confirmed backup or apply the documented corrective action.

## Content migration rules

- Export only the rows and tables required for the change; do not replace the whole production database for a content-only update.
- Treat uploaded files and their database rows as one change. Verify both before and after execution.
- Preserve production-owned timestamps, ids, and publication state unless the task explicitly changes them.
- Use UTF-8 SQL files and inspect Czech text after import.
- Never place credentials, password hashes, API tokens, production exports, or personal data in Git or Taskino.

## Password and access recovery

Use the local Docker `reset-admin` helper only for local development. Production access recovery must go through an authenticated hosting/database administration session and a separately approved Taskino task. Never deploy a public password-reset helper.

## Blueboard `.deploy`

Blueboard's `.deploy` file is a directive list, not a general shell script. Keep it limited to documented Blueboard directives. The current file deliberately contains:

```text
composer install
delete www/temp/cache
```

Arbitrary `php`, SQL, or shell commands do not belong there. Database execution remains an explicit, authenticated step with a recorded checksum and verification result.
