# Page-owned media consistency

Task `c0dcf39c`, local implementation 2026-09-18; pending human Verification.

## Findings and changes

An isolated reproduction deleted a page but left its picture row. The schema
cascades media rows; pictures has no page foreign key. Existing page controls
also deleted the page before recursively removing only its media directory.
Uploads moved originals before inserting records, and pictures inserted their
record before thumbnail generation. Duplicate uploads could overwrite bytes
while leaving old metadata or thumbnails.

`MediaStorage` now coordinates both page Dropzone upload forms, individual
media/picture deletion and `Document::delete` (all three page-list controls):

- Uploads validate the target page, filename, upload status and existing image
  policy, then stage the original and thumbnail before publishing files and
  inserting metadata. Duplicate names return 409 without overwriting files.
- Operations lock the page row inside a database transaction. File restoration
  on a caught failure happens before that lock is released. An insert failure
  removes newly placed files; thumbnail/move failures leave no committed row.
- Deletions stage originals/thumbnails or both page directories, then remove
  records transactionally. SQL failure restores files. Missing files do not
  prevent deletion of an existing page's stale media reference.
- Page deletion explicitly deletes its picture rows and both owned directories.
  Child pages retain the existing schema's SET NULL behavior and their files.
- Paths are confined to numeric page directories; symbolic links and unsafe
  path components fail closed. Permission and CSRF guards remain in the callers.

## Read-only diagnostics

```sh
php console.php media:audit
php console.php media:audit --details
```

Default output contains counts; `--details` adds record IDs and relative paths.
The report separates missing originals, missing thumbnails, orphan rows,
unreferenced files, unsafe paths and pending staging operations. It does not
delete or repair anything. The local pre-fixture inventory returned zero for
all categories. That is not evidence about production data.

Scope is page-owned `media/<id>` and `pictures/<id>`. Standalone `images`, menu,
carousel, editor assets and elFinder's independent mutations are outside this
change. Existing orphan rows with no live parent require separately reviewed
repair; the normal mutation API does not infer their ownership.

## Failure and deployment limits

`www/temp/media-operations` must be writable and protected from HTTP access
(the current deployment denies access to `www/temp`). Same-filesystem staging
is required. No schema migration is needed; rebuild generated Nette DI and
RobotLoader caches when deploying the new classes/console registration.

Caught filesystem/SQL failures have tested rollback. A process crash, ambiguous
commit result, restoration failure or post-commit purge failure cannot be made
atomic across a database and filesystem. Staging journals remain for diagnosis;
purge/restore failures are logged. Inspect `media:audit`, the database and the
operation directory before any manual recovery. Never blindly restore or purge
pending operations. Concurrent public reads may briefly observe staged files as
missing; unrelated filesystem writers do not participate in the page lock.

## Verification

- Ten Nette Tester files pass on PHP 7.4.33. `MediaStorageTest.phpt` covers failed
  writer/thumbnail, real SQLite insert/delete/page-delete errors, restoration,
  missing files, duplicate protection, diagnostics and page/picture cleanup.
- Real HTTP/MariaDB checks cover protected media/picture multipart uploads,
  invalid CSRF rejection, correct file sizes/bytes, thumbnails, duplicate 409,
  individual picture deletion and page deletion with both file families.
- All test records, files and temporary accounts are cleaned up. No production
  repair, schema change, deployment or commit was performed.

Reproduce locally with a unique prefix; always clean accounts even on failure:

```sh
docker compose exec -T app php tests/manual/admin-permissions-fixture.php create permtest-1234abcd
python tests/manual/media-http.py permtest-1234abcd mediatest-1234abcd
docker compose exec -T app php tests/manual/admin-permissions-fixture.php cleanup permtest-1234abcd
docker compose exec -T app composer test
```

Human Verification should check Dropzone success/error feedback and page/media
deletion in the UI. HTTP checks do not establish visual browser behavior.
