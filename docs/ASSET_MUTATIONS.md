# Non-page asset mutations

Task `bc1cecb6`, September 25–26, 2026; corrected October 21 after the
October 20 verification (legacy file names, actionable errors, carousel insert,
inventory gaps) and on October 24 after the owner's decision on the PHP 7.4 name
check (names that start with a non-ASCII letter, local exif). Local changes await
Verification/release. No production repair, schema migration or broad storage
replacement.

## Inventory and ownership

| Writer | Prior failure mode | Current behavior |
| --- | --- | --- |
| Files DropUploadControl / FilesPresenter delete | Upload could overwrite an existing image; deletion concatenated a request path | Duplicate upload returns 409; only a standalone image filename can be deleted; traversal and symlinks rejected |
| Carousel insert/edit/delete | Existing filename removed before unchecked copy; SQL failure could strand a file; deletion removed the row first | Unique generated image name; staged old/new files and transactional metadata; shared legacy images retained until their last owner is deleted/replaced |
| Appearance SavePathsControl | Unchecked copy, wrong doubled www cleanup path, metadata updated independently | Valid local_path owner required; unique image name and transactional replacement; shared setting paths retained |
| Menu image upload/delete | Four unchecked copies could partially replace a set; delete accepted arbitrary suffix/path | Checked staged group replacement; positive owner ID and explicit suffix allowlist |
| Menu node/group deletion | Foreign-key cascades left descendant icons | Stage all four image variants for affected nodes and descendants before deletion; rollback on SQL failure |
| Page deletion cascading into menu | Menu rows disappeared but their icons remained | Existing MediaStorage deletion stages affected menu icons too; rollback shares the page transaction |
| Links edit/delete/image-delete | Metadata changed before file validation; prior image deleted before unchecked upload; row deletion left its image | Existing owner required; staged fixed-path replacement/deletion coordinated with the row transaction |
| elFinder independent mutations | Could modify page media without updating metadata; hidden managed directories were not explicitly read/write denied | Media root read-only; managed image directories read/write denied and locked; independent images still support normal operations, with overwrite options disabled |
| Page Dropzone media/pictures | Already protected by MediaStorage | Existing upload/thumbnail handling retained; integration regressions rerun |
| Legacy Pages ImageUploadControl | Unchecked originals/thumbnail writes | No first-party presenter factory/caller found; remains dormant. Any future wiring must use MediaStorage first |
| Console `pictures:thumbnail-generator` (`app/console/Pictures/ThumbnailGeneratorCommand.php`) | Writes missing `www/pictures/<page>/tn/<name>` thumbnails outside the web request; no lock, staging or journal | Not changed. It only creates absent thumbnails (never overwrites an original or an existing thumbnail), so a failure leaves at most a missing or partial thumbnail that the next run can recreate once removed. Run it outside media edits; its failure diagnostics belong to task `01c4ce6c` |
| Navbar menu icons `www/images/menu/icons/<menu id>.png` | Read by `NavbarMenuControl` when present | No first-party writer or cleanup: files are placed by hand or deployment, and menu node/group deletion does not remove them. The `menu/` subtree is a managed directory, so elFinder cannot change it. Excluded deliberately; a stale icon only shows for a reused menu id |
| Contacts/profile and other first-party forms | Inventory found no additional active image writer | No speculative rewrite |

### Legacy file names (October 21)

Carousel rows and appearance paths saved before upload names were sanitized
(commit `6941702`) hold raw names with spaces or diacritics, and elFinder allows
such names in `images/`. New names stay strict (`[A-Za-z0-9][A-Za-z0-9_.-]*`), but
removing an existing file accepts any plain basename: no `/`, `\`, NUL or other
control characters, and no leading dot (which also protects `.htaccess` and
elFinder's `.tmb`). Traversal and symlink checks are unchanged. Until October 21
the strict check ran before the existence check, so deleting such a carousel
row, replacing its image, replacing such an appearance path, or deleting such a
file on /admin/files/ answered 404 and changed nothing. If a stored name is not
even a plain basename, the row operation still completes, the file is left in
place and a warning names only the directory.

### Names that start with a non-ASCII letter (October 24)

PHP 7.4's `basename()` and `pathinfo()` depend on the locale. In the C locale
(the local container and most web servers) `basename('Žluť.png')` is `luť.png`
and `basename('Čáp.jpg')` is `p.jpg`; PHP 8 is not affected. The /admin/files/
delete compared `basename($path)` with the path, so on 7.4 such a file answered
400 and stayed, and the page built its delete form from `basename($key)`, so
the card of `Čáp.jpg` posted `p.jpg` and could delete another file of that
name (it also showed and linked `p.jpg`). Now:

- `FilesPresenter::handleDelete` rejects any `/` or `\` with 400 (that is what
  keeps `menu/1.png` out of the managed folders); dotfiles, `.`/`..`, control
  characters and symlinks are still refused by `AssetStorage`'s removal checks,
  which are unchanged;
- `Files/default.latte` names, links and deletes each file by
  `SplFileInfo::getFilename()`, which does not depend on the locale (unlike
  `getBasename()`). The admin `toBaseName` filter has no other caller and is
  left as it was.

Other `basename()`/`pathinfo()` calls in `app/`, none on a delete path:
`IO::sanitizeUploadFileName` (upload names) uses both, but on 7.4 the same
locale makes its `iconv` transliteration turn every non-ASCII letter into `?`,
which is then removed, so the result is the same with or without `basename()`
(`Žluť.png` → `lu.png`, `Čáp.jpg` → `p.jpg` on 7.4.33; `Zlut.png`, `Cap.jpg` on
8.2.33). That transliteration is a separate, pre-existing limitation and is not
changed here. `AssetWrites` calls `pathinfo()` only on already sanitised ASCII
names; `AssetStorage`, `MediaStorage` and `MediaAudit` call `basename()` on
internal stage paths for log lines.

/admin/files/ calls `exif_imagetype()`, and the local app image had no exif
extension, so the page stopped there with a 500 sent as 200. The local
`docker/php/Dockerfile` now builds exif in its own layer (the archived Bullseye
mirrors answer 404 for a changed apt layer; exif needs no system packages).
Production needs exif as well; release `c8e1d96d` has a read-only check.

### Errors users see (October 21)

Every rejection now has a status code and a Czech message:

| Case | Status | Shown as |
| --- | --- | --- |
| Not an allowed image (PNG/JPG/GIF/BMP, name up to 140 characters) | 400 | Form error from the MIME rule for non-image bytes; flash for an image the storage check rejects; Dropzone message |
| Name already exists | 409 | Dropzone message ("Soubor s tímto názvem už existuje…"); elFinder renames instead |
| Interrupted or oversized upload | 400 | Flash or Dropzone message |
| New carousel slide without an image | 400 | Flash "Vyberte obrázek nové položky karuselu." (no row) |
| Invalid name, symlink or directory on removal | 400 | Error page; only reachable by tampered requests |
| Owner row missing | 404 | Flash on carousel delete; error page elsewhere |

Forms (carousel insert/edit, appearance paths, link edit, menu images) catch the
`BadRequestException` and show its message as a flash after the redirect.
Dropzone handlers (standalone images and the page media/pictures Dropzones) answer
JSON `{"error": "<message>"}` with the status, and Dropzone shows the text next
to the file. Before, Nette defaulted these exceptions to 404, so Dropzone
displayed `true` and forms ended on a 404 or generic 409 page. MediaStorage's
upload rejections got the same codes and messages; its storage behavior did not
change.

### Carousel insert (October 21)

Adding a slide always failed with HTTP 500 on strict MariaDB: the insert set
neither `sorted` (NOT NULL, no default) nor `image` when nothing was uploaded.
New slides now require an image and are appended with `sorted` = current
maximum + 2, matching the manager's step of two.

`AssetStorage` provides a confined non-page file boundary with checked staging,
backups, rollback and a protected journal. `AssetWrites` makes the ownership
decisions for carousel/settings/links/menu deletion. The current role and CSRF
checks remain in the presenters. Input image validation still uses the existing
extension, real-upload and MIME checks.

## Failure and concurrency limits

`www/temp/asset-operations` must be writable, outside HTTP access and on the same
filesystem as final assets. First-party asset writes, elFinder and page deletions
share an advisory filesystem lock. Each operation stages files and performs SQL
inside a transaction; caught failures restore files before releasing the lock.
Unrelated direct SQL/filesystem writers do not participate.

The journal records intended moves before execution. A failed commit may have
an ambiguous outcome, and failed restoration or post-commit purge retains the
operation directory. Do not blindly restore/delete these directories: inspect
the actual row references and files against the journal first. Diagnostics name
the stage/phase/error category without copying submitted data or raw SQL errors.
This is caught-failure consistency, not atomicity across a process crash and two
storage systems. Task `807a6cc8` owns the separate recovery rehearsal.

Existing stored files are not bulk renamed or repaired. New carousel/appearance
names are generated to prevent collisions and fit the existing database columns.
Ordinary standalone uploads reject duplicate names. elFinder may choose a unique
name for a duplicate instead of overwriting; its media root is for selection and
reading, while page media mutations go through the CMS's managed controls.
Free-form editor HTML can reference independent images; deletion of such images
cannot infer every HTML reference and remains an explicit user action.

## Validation

`AssetStorageTest.phpt` checks duplicate refusal, failed writer/multi-file
replacement, real SQL insert/delete failures, restoration, shared carousel
ownership, menu cascades, path/symlink denial and redacted diagnostics.
`MediaStorageTest.phpt` additionally checks restoration and cleanup of menu icons
when a page delete fails/succeeds. The active non-page writers do not generate
thumbnails; existing page-thumbnail failure tests remain the relevant coverage.

`tests/manual/asset-http.py` uses real rendered forms and disposable rows/files:
standalone upload/409, traversal rejection, carousel replacement, appearance
settings, link and menu images, denied role, file-manager media write denial,
and record/file cleanup. Use the disposable account setup from ADMIN_CSRF.md.
It never rewrites a real appearance setting; the fixture creates its own row.
The existing admin CSRF, patched elFinder and page-media HTTP checks also pass.

October 21 additions: `AssetStorageTest.phpt` covers legacy-name removal
(spaces, diacritics, `a..b`), rejected names with status 400 (`.htaccess`, `.`,
`..`, NUL, backslash, newline), strict new names, 409/400 codes and messages,
legacy carousel deletion, an unusable stored name that keeps its file but deletes
the row, and the 400 for a slide without an image. `asset-http.py` adds readable
Dropzone JSON for duplicate/fake images, deletion of a spaced name and refusal of
a dotfile on /admin/files/, legacy-named carousel and appearance replacement and
deletion, the MIME-rule form error and storage flash, and carousel insert with
and without an image. `media-http.py` checks the page Dropzone messages.

October 24 additions: `FilesDeleteTest.phpt` runs `FilesPresenter::handleDelete`
with the real `AssetStorage` on a temporary root: `Žluť.png` is deleted while
`luť.png` stays, `Čáp.jpg` is deleted while `p.jpg` stays, and `menu/1.png`,
`menu\1.png`, `.htaccess`, `../outside.png`, `..\outside.png`, `..` and an
empty path are 400 with every file kept. With the old basename comparison the
test fails on `Žluť.png` ("Invalid asset path."). `asset-http.py` renders
/admin/files/ (complete page, now that exif is installed), deletes
`Žluť-<prefix>.png` and `Čáp-<prefix>.jpg` through their rendered forms (303,
files gone) while `p-<prefix>.jpg` stays, and checks that separators into
`menu/`, traversal, `..` and an empty path are 400. See the task `bc1cecb6`
result note for the counts and the browser checks.

October 21 results: all 16 Tester files pass and 195 PHP/PHPT files lint clean on
PHP 7.4.33, 8.0.30 and 8.2.33; `asset-http.py`, `media-http.py`,
`csrf-upload-http.py` pass. In Chrome: deleting a carousel row whose image is
`fix legacy.jpg` and replacing the image of another legacy row both work, adding
a slide with an image creates a sorted row with a hex-named file, and the page
images Dropzone shows "Soubor s tímto názvem už existuje…" (409) and "Neplatný
obrázek…" (400) with the stored file byte-identical. The /admin/files/ Dropzone
could not be used in the local browser then: that page stopped at the local
`exif_imagetype` error before its scripts loaded (fixed locally on October 24).

Historical September 26 counts: 15 Tester files, 193-file lint; no browser then.
All fixture records/accounts and files are removed after the checks. Rebuild
RobotLoader/DI/Latte caches when deploying the new classes. Release together
with the prepared elFinder patch.
