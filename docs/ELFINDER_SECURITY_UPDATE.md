# elFinder security update

Task `299e3de4`, September 24–25, 2026. Local implementation, pending user
Verification and release `c8e1d96d`; no production deployment or PR merge.

Composer now requires `~2.1.70` and locks `studio-42/elfinder` to **2.1.70**,
upstream commit `e7ea668fd569fc9903fb1c431d47d58f2daad2f2`. No other locked
package changed. This resolves the user-reported dependency alerts:

- [CVE-2026-81889](https://github.com/advisories/GHSA-8x3q-jpjh-qh5c):
  URL-upload SSRF. The patched connector refuses URL uploads without cURL.
- [CVE-2026-81890](https://github.com/advisories/GHSA-9hjf-w35w-6vx2):
  `netmount` now requires the connector CSRF token.
- [CVE-2026-81891](https://github.com/advisories/GHSA-gxmj-r5rf-ggwq):
  archive extraction normalizes filename MIME types before the upload filter.

The [upstream release](https://github.com/Studio-42/elFinder/releases/tag/2.1.70)
also patches browser-side behavior. The committed admin bundle still contained
elFinder **2.1.16**, so a PHP-only upgrade was insufficient. Its elFinder/Czech
locale inputs are regenerated from the locked package. Matching CSS/images/
sounds live in `www/elfinder` (not under a `vendor` path, which some hosts deny for
every URL); the dialog uses that base URL and the new
CSS after the legacy stylesheet. `all-back.js` now has a content-hash URL, also
used to refresh elFinder CSS after a release. Existing CMS and connector token
forwarding and the root permission checks remain in place.

## Bounded build

`gulp scripts-elfinder` invokes `tools/build/elfinder.cjs`: it validates the source
client version against the lockfile, replaces the two elFinder bundle inputs
between explicit library boundaries, and copies official visual assets. The
other concatenated libraries are preserved byte-for-byte. This avoids unrelated
dependency upgrades while the full reproducible frontend build remains task
`55189b13`. The normal `scripts-a` task still builds the full bundle from inputs.
The refresh uses Node 16.7+ (`fs.cpSync`); the local build used Node 22.15.0.

With Composer and frontend build dependencies installed:

```sh
npx gulp scripts-elfinder
node --check www/js/all-back.js
```

The local Docker installation stores Composer dependencies in a named volume.
For this run, `docker compose cp` exported that exact installed package into
ignored `www/temp/elfinder-source`. `ELFINDER_SOURCE` selected it; a disposable
Gulp 4.0.2/gulp-concat 2.6.1 toolchain in `www/temp/elfinder-build` supplied Gulp.
Neither temporary directory is a deployed source dependency. Two consecutive
refreshes produced identical bundle hashes. Do not manually edit the bundle.

## Dot files and overwrite (October 24, task `4bec789e`)

Both roots set `'accessControl' => 'access'`, copied from elFinder's sample
connector, but no `access()` function exists (nor at HEAD), and elFinder ignores
a non-callable value. Dot files were therefore listed, readable, writable and
deletable: `cmd=rm` removed a disposable `www/images/.permtest-…-rmprobe`.
`www/images/.htaccess` is what blocks scripts, HTML and SVG under `/images`, and
the images root accepts SVG uploads. Now:

- the dangling `accessControl` is removed, and the shared attributes get a
  rule (any path segment inside the root that starts with a dot) with `hidden`,
  `read` false, `write` false and `locked`. Dot files are not listed (`open`,
  `ls`, `tree`, `search`), their content is not served (`file`, `get`), and `rm`,
  `rename`, `duplicate`, `put`, `paste` (copy or cut) and upload onto them
  (also through `renames[]`, `upload_path[]` or chunks) are refused. `info` still
  answers for a known hash, only as hidden, unreadable and locked; `url` only builds
  a link from a name the caller already knows, the web server answers 403 for dot
  files, and a one-time link can't read a local file. New dot names were already
  refused by elFinder's `acceptedName` (`mkdir`, `mkfile`, upload, archive entries);
- elFinder still writes and serves its `.tmb` thumbnails (it writes them
  directly, not through these attributes);
- `uploadOverwrite => false` was not enough: elFinder applies the client's
  `overwrite` parameter over it (`elFinder.class.php:3394-3395`), so a crafted
  `overwrite=1` replaced an existing file. An `upload.pre` handler now forces
  `overwrite` to `0`, so a duplicate name always gets a `-1` suffix.

Corrections of November 5, after the October 24 verification:

- **`archive` and `zipdl` leaked dot files.** Both check `read` on the selected
  items only and then zip the tree natively, so a hidden file inside a selected
  folder ended up in the ZIP, which `archive` saves in the public folder. A
  handler now refuses both when a selected folder has a dot file anywhere inside it,
  or when a whole root is selected, with a message saying why. Other folders still
  zip. After the refusal of "Create archive" the client returns to its name field,
  as it does after any server error while creating something: Esc cancels, and a
  click elsewhere sends the request again and shows the same message.
- **The dot rule also matched paths outside the root.** elFinder passes those as
  `//absolute/path`, and extraction checks every file in its temp directory. With
  `vendor/studio-42/elfinder/php/.tmp` writable (elFinder's default temp, likely on
  shared hosting) every extracted entry was dropped, and extract silently produced
  nothing. The rule now covers paths inside the root only (`~^/(?!/)(?:.*/)?\.~s`, see
  November 6 for the `s`). Dot entries in an archive are still refused, by
  `acceptedName`.
- **elFinder's temp directory is now `temp/elfinder`** (`commonTempPath`), outside
  the document root and normally on the same filesystem as the roots; if it can't be
  created, elFinder falls back to the system temp. From a different filesystem,
  rename fails for folders: extract into a new folder answered `errExtract` and
  folders inside an archive were silently lost. Both work now.
- **Non-ASCII names on PHP 7.4.** In a non-UTF-8 locale PHP 7.4's `basename()` drops
  leading non-ASCII characters, and elFinder's default `en_US.UTF-8` isn't installed
  in every image: `Čáp.png` was listed as `p.png`, and a second upload replaced the
  file despite `overwrite=0`. The connector now selects the first available of
  `C.UTF-8`, `en_US.UTF-8` and `cs_CZ.UTF-8`. If the web runtime has none of them,
  PHP 7.4 still has the problem (release `c8e1d96d` checks this); PHP 8.0 and later
  don't.
- **SVG is always an attachment through `cmd=file`.** libmagic reports an SVG without
  an XML declaration as `image/svg`, and elFinder's default `dispInlineRegex` served
  that inline. Headless Chrome 155 ran no script for it, but Firefox and Safari were
  not tested, so `dispInlineRegex` now excludes SVG instead of depending on the
  browser. Direct `/images/*.svg` was already 403 (the `.htaccess`).
- **Downloading a folder as a ZIP works again.** The client fetches the prepared ZIP
  with a plain GET link, which the CMS CSRF check refused with 403. `cmd=zipdl` with
  `download=1`, the only value the client sends, is now part of its read allowlist.
  Preparing a ZIP needs the CMS token: elFinder prepares one whenever `download` is
  empty after trimming, so `download=0`, `download=` and `download=%200` are not
  exempt (403, nothing prepared). The prepared file comes from the session's own
  entry, except on Chrome for iOS (see November 6).

Corrections of November 6, after the verification of that day:

- **The dot rule didn't cross a newline.** Without the `s` modifier `.*` stops at a
  newline, so a dot file inside a folder with a newline in its name was listed,
  readable and deletable. The rule has the modifier now, and paths outside the root
  (`//absolute/path`) still don't match.
- **Control characters in new names are refused** on both roots, through
  `acceptedName` (`ElfinderControl::ACCEPTED_NAME`): `mkdir`, `mkfile`, `rename`,
  upload and archive entries. It is elFinder's Linux default for the local driver (no
  leading dot, no `/`) without control characters, including DEL and C1. A folder that
  already has such a name stays usable and can be renamed or deleted.
- **A crafted request could skip every handler in this file.** elFinder registers a
  named key such as `archive.pre` only when `$_POST['cmd']` names the command, while
  the connector also takes `cmd` from the query string of a POST. With both tokens,
  `POST …&cmd=archive` saved a dot file in a public ZIP and `POST …&cmd=upload` with
  `overwrite=1` replaced a file. The handlers are now bound through `*.pre`, which
  elFinder always registers.
- **Chrome on iOS.** With `CriOS` in the User-Agent, elFinder takes the file from
  `targets[1]` instead of the session, because that browser fetches a ZIP twice
  (`elFinder.class.php:1951`), and then serves and deletes it from its temp directory.
  So any signed-in media or pictures user could download and delete a named file there,
  without a token, by sending that User-Agent. A handler now answers 404 unless
  `targets[1]` is a prepared ZIP's name (`elfzdl-` and letters or digits), which also
  removes the notices for `.` and `..`. The two-fetch flow still works over HTTP with
  that User-Agent; it was not tried on a real iPhone. What remains: the name isn't
  tied to a session, so someone who knew another session's prepared ZIP could fetch it.
  The name has six random characters, lives until the fetch or the 2-hour cleanup, and
  is only ever sent to the session that prepared it.

Corrections of November 7, after the verification of that day. These supersede the
claims they name above:

- **A forged `//absolute/path` hash bypassed the dot and managed-folder rules
  (regression from November 5).** An attribute pattern cannot cover it: elFinder
  prepends `/` to a path `relpath()` could not strip, so both the out-of-root
  extraction temp path *and* a `//`-spelled in-root hash arrive as the same `///…`
  relpath, and the `(?!/)` that lets extraction through also lets the forged hash
  through. A real `accessControl` callback (`ElfinderControl::accessDecision`) now
  decides from the canonical real path: a dot file or a managed folder reached by any
  spelling of an in-root path is hidden, unreadable, unwritable and locked, while an
  out-of-root temp path is deferred to `acceptedName` and the defaults so extraction
  still works. The attribute rules stay as defence in depth. This corrects the
  November 5 claim that "the rule covers paths inside the root only": a `//` spelling
  of an in-root path was not covered. A callback was chosen over a first `~^///` rule
  because such a rule would also match every extraction temp path and silently drop
  extracted files again.
- **`zipdl` did not leak; its refusal is removed (owner decision 1).** The November 5
  note was wrong that "`archive` and `zipdl` leaked dot files". `zipdl` walks with
  `getItemsInHand`, which checks `read` on every item (`elFinderVolumeDriver.class.php`
  `:4356`), so hidden entries - dot files, managed folders, unreadable items - are
  skipped; only `archive` leaked. A folder with a dot file now downloads as a ZIP
  again, without its dot files, and a whole root excludes `.htaccess` and the managed
  folders. `archive` keeps its refusal.
- **`archive` handler fixed for its neighbours (D2, D6, D7).** It is refused when the
  selected items do not share one parent (`archive()` takes the folder from the first
  target and only the basename of the rest, so a same-named sibling that holds a dot
  file would be zipped instead), when the tree contains a symlink (`zip`/`tar` follow
  it out of the root, unlike the directory walk), and when a subfolder is unreadable
  (a refusal now, not the `RecursiveDirectoryIterator` 500 the walk used to throw).
- **`extract` of an archive sitting directly in a root is forced into a new folder
  (D5).** In place, a `carousel.zip` cut to the root merged its `carousel/` entries
  into the managed `carousel` through `localMoveRecursive`, past the locked attribute.
  Forcing `makedir=1` at the root level lands the content in a new, uniquely named
  folder instead. That check compares the target's path with the roots, so a forged
  `//` target skipped it and still merged into `carousel` (N2); such a target is now
  unreadable and refused (second round below). The neighbouring routes (`paste`,
  `rename`, `upload_path[]`) into a managed folder were already refused by the
  hidden/locked attribute.
- **The CMS token exemption reads the connector's own view (D3).** It now decides from
  `$_SERVER['REQUEST_METHOD']`, `$_GET['cmd']`, `$_GET['download']`, `$_POST` and
  `$_FILES` - the superglobals the connector executes from - plus an empty raw body,
  not Nette's parsed request. A forged query key (`cmd=open&%20cmd=zipdl`), a
  NUL-truncated one, a `download` trick, and `X-HTTP-Method-Override: GET` can no
  longer make Nette and the connector disagree. This corrects "every elFinder POST
  requires the CMS token" to a decision made from what elFinder itself would run.
- **One-time URLs are disabled (D4).** `onetimeUrl => false` on both roots: a crafted
  `cmd=url&options[onetime]=1` no longer writes a pointer in the temp directory, and a
  chunked `upload` accepts only elFinder's own chunk name (a `.part` with an uploaded
  file, or an `ELF…` merge name without one), so an arbitrary name can't pull a
  planted temp file into a root. `onetimeUrl` does not gate `cmd=file&onetime=1`,
  though: elFinder still served, and then deleted, the temp file that any pointer there
  names, and a partial chunk upload writes a file with a pointer's name and content
  (N3). Since the second round that command answers 404 in every session. The
  "one-time link can't read a local file" and "a named temp file is neither served nor
  deleted" claims held for `zipdl` only.
- **The prepared-ZIP name is bound to the session that prepared it (owner decision
  4).** A `zipdl` listener records each `elfzdl-…` name in the session; the Chrome on
  iOS fetch-by-name accepts only a name this session prepared, so another session that
  knows the name gets 404. The two-fetch flow still works (simulated by User-Agent).
  Not changed: the 2-hour garbage collection still only runs when another ZIP is
  prepared, so a file can outlive two hours; this is left as a minor housekeeping gap.

Second round of November 7 (agent-verifier note `abd47f9f` and the owner's decisions of
that day). Where it differs, this supersedes the first round above:

- **Any in-root path reached in the `//` form is refused (N1, N2).** elFinder locks
  only the canonical root path, and the callback above denied only dot files and
  managed folders, so a forged `//` spelling of a volume root was unlocked: `rm`
  removed a whole root, `.htaccess` included, and `rename` moved it out of the volume.
  A canonical in-root path never reaches `attr()` in that form, so `accessDecision`
  now denies every path that resolves into a root - the root itself, ordinary files
  and folders, dot files, managed folders - as hidden, unreadable, unwritable and
  locked. It resolves through the nearest existing folder, so a new name in a folder
  that doesn't exist yet is covered too. Paths outside both roots still defer, so
  extract keeps working, also with a dot in the temp path. `extract` checks read on
  its target and write on the folder, so this also refuses the forged `extract`
  target of N2. Not every command reads these attributes: `dim`, `url` and
  `duplicate` don't check read, `subdirs` has no stat at all, and `tree` doesn't
  check read on its own target (verifier note `0eccf45a`). `duplicate` is refused in
  the pre-handler since the third round (below); the others are item A of Backlog
  issue `0c4539cc`.
- **`cmd=file` with a non-empty `onetime` answers 404 (N3).** See D4 above. Nothing in
  the CMS uses one-time links.
- **The client's cleanup after a failed chunk is accepted (N4).** It carries no file,
  `upload[]=chunkfail`, `mimes=chunkfail` and the part's name; it was refused as an
  invalid chunk, so the parts stayed until elFinder's one-hour cleanup. elFinder now
  deletes that upload's own parts.
- **A nested upload file field is refused (N5).** An uploaded file sent as
  `upload[a][b]` puts arrays where names belong; on PHP 8 the chunk check threw a
  `TypeError` (a 500). When the uploaded files' names aren't a flat list, the upload
  is refused with `errUpload`, with or without a chunk. That is all this covers: the
  same shape sent as an ordinary POST field, and other arguments of the wrong shape,
  still give a 500 on PHP 8 (item B of `0c4539cc`).
- **A folder made by extract is 0755 (N6).** elFinder renames its 0777 quarantine
  folder into place. An after-handler, bound through `'*'` for the same reason as the
  pre-handler (a named key only registers when `$_POST` names the command), gives an
  added folder 0755, like one made in the File Manager. Only that folder: under
  `unzip` (the PHP 7.4 image), the folders and files inside it, and the files an
  in-place extract adds, keep the modes stored in the archive (777, 770 and 666
  seen); with `ZipArchive` they get 755 and 644 (item E of `0c4539cc`). Since the
  third round, a folder that existed before keeps its mode (N11, below).
- **elFinder's worker scripts ship (from `299e3de4`, same release).**
  `tools/build/elfinder.cjs` also copies `js/worker`, so `/elfinder/js/worker/*`
  is served; without it the Info dialog showed a NetworkError in its md5 and sha256
  rows. The bundle is byte-identical after the refresh.

Third and last round of this task, November 8 (agent-verifier note `0eccf45a` and the
owner's decision of that day):

- **`duplicate` is refused unless the client would offer it (N8).** elFinder's
  `duplicate` checks only that the target exists and that the new name may be
  created. It reads ".env" as an empty name plus an extension, so a dot file named
  with letters and digits only (`.env`, `.htaccess`) was copied to " copy 1.env":
  listed, readable and served at its direct URL. In the read-only media root it
  created files although `mkfile` and `put` are refused there. The pre-handler now
  refuses the whole request, with nothing created, unless every target is readable,
  visible and not a volume root, and the folder it would be copied into is writable.
  So dot files at any depth, managed folders, `.tmb`, forged `//` paths, the roots,
  and everything in the media root are refused. This is the rule the elFinder 2.1.70
  client applies before it offers Duplikovat, so the File Manager loses nothing. An
  ordinary file or folder in a writable folder still duplicates, and a folder's copy
  leaves out the dot files inside it, as before.
- **A folder an in-place extract merges into keeps its mode (N11).** An in-place
  extract lists the existing folders it merged into as added, and the N6 handler gave
  them 0755 (700 became 755). The pre-handler now notes the folders that already exist
  where the extract lands, and the after-handler changes only the others.
- `elfinder-security-http.py` in copy mode requires `ELFINDER_HTTP_BASE` and refuses
  port 8090, so it can't fall back to the real stack.

What is still open about elFinder after this task is collected in Backlog issue
`0c4539cc`.

Left as they are, on purpose: deleting a folder deletes the dot files inside it
(elFinder removes the tree natively; a root itself is locked, in every spelling since
the second round, so `www/images/.htaccess` stays); extract into the current folder replaces existing files
after the client's "replace old files with new ones?" question; and the client's
backup mode (`renames[]`) keeps the old file under another name. Still open and not
touched this round: SVG is always served as an attachment; the `temp/elfinder`
location; locale selection; folder delete removing inner dot files; extract replacing
after the client's question; the `renames[]` backup; and `\p{Cf}` names such as
U+202E, which `acceptedName` still accepts.

## Checks and limits

Final results: 17/17 Tester files pass on PHP 7.4.33, 8.0.30 and 8.2.33;
196-file lint on the same versions, generated JavaScript syntax and patch whitespace
pass.
Composer validation passes with the existing deprecated GPL identifier warning.
`composer audit --locked` reports zero advisories; its nonzero status is due to
the existing abandoned `nette/finder` and `nette/tokenizer` packages. Those
maintenance findings remain in dependency audit task `3f826a8b`.

`ElfinderSecurityTest.phpt` uses inert temporary files and no production config.
It checks actual extracted-file filtering for phtml/phar/php5/php3 while ordinary
text survives, the connector's netmount CSRF gate, and no-cURL refusal in a
child process. DNS/socket methods in that child are traps, so the test cannot
contact external services. The fixture does not execute uploaded PHP.

Local `elfinder-security-http.py` verifies anonymous/restricted denial, a valid
CMS token with missing connector token for netmount, archive safety, normal
text upload/removal, and served client version/cache hash. Since October 24 it
also checks the dot-file rule in both roots with disposable dot files (never the
real `.htaccess`, whose SHA-256 it compares before and after), the forced
no-overwrite upload, mkdir, rename, a PNG upload with its `.tmb` thumbnail
served with 200, and recursive delete. Since November 5 it also covers `tree`,
`search`, `url`, `renames[]`, `upload_path[]`, chunked uploads, writes to the
media root, `archive` and `zipdl` (refused with a dot file inside or on a root, and
a clean folder zipped with its content only), extract into a new folder and a
folder inside an archive, a duplicate non-ASCII upload and SVG disposition. Since
November 6 it also covers a dot file in a folder with a newline in its name (with a
plain file next to it as the control), control characters in new names, `cmd` in a
POST's query string, `zipdl` without tokens for each value of `download`, and the
`CriOS` branch: refused names and the two-fetch flow. Since the second round of
November 7 it sends no mutating command to a real root, `.htaccess` or a real managed
folder in any spelling: root-level checks use a disposable media root selected with
`?path=<number>`, and the checks that have to write at the images root itself (a
forged root, extract in the root, archive of a whole root) run only against a
disposable copy of the tree (`ELFINDER_HTTP_COPY`, see the script's docstring). It
also covers N1-N6 and the worker files. Since November 8 its dot fixtures include
letters-only names (`.env`, `.htpasswd`), so its `duplicate` checks cover N8, in both
roots and, in the copy, at the images root; it no longer claims `dim`, which ignores
read (item A1 of `0c4539cc`). The same script passes against disposable
copies served by PHP 8.0.30 and 8.2.33 through `php-readiness-router.php` (the
readiness image with zip and gd added); PHP's built-in server doesn't apply
`.htaccess`, so those runs skip the direct-URL 403 checks. `ElfinderSecurityTest.phpt`
checks the dot rule's matches, including newlines (the same path without the `s`
modifier is the control), that extraction keeps files when the temp path has a dot in
it (also with the access callback), `acceptedName` on a real volume, the `CriOS` name
check, and, on a real volume with the callback, that a forged root and file are locked
and refuse `rm`, `rename` and `mkdir` while the canonical file stays removable. Since
November 8 it runs `duplicate` through elFinder's own `exec()` on two disposable roots
set up like the connector's: refused for dot files (letters-only and not), a managed
folder, `.tmb`, both roots, the read-only media root and forged paths, with nothing
created, while an ordinary file and folder still duplicate; and it checks that an
in-place extract leaves an existing folder's mode alone (N11). With the archive
handler or the locale option removed, the HTTP check fails as expected; with the
previous `!/\.!` rule, extraction drops every file. Each fix of November 6 was also
undone in turn, and the harness stopped at the matching check. The existing
`elfinder-csrf-http.py` raw-body substitution and `csrf-upload-http.py` checks
also pass. Use the disposable account setup in `docs/ADMIN_CSRF.md`; each script
cleans its files and account cleanup belongs in a finally block.

The current connector permits images/text, so arbitrary ZIP upload is already
denied; the archive check covers an existing inert ZIP placed in a disposable
folder. CMS authorization and Apache upload protections provide additional
boundaries, but do not substitute for the upstream update. Production extension
availability and exact reachable features have not been claimed as verified.

Browser results are in the task notes of `299e3de4` and `4bec789e`. Human review must
still check icons/layout, Czech labels, image selection into Summernote and
retained-cache reload. The existing elFinder/browser tasks retain their wider
boundaries.

On November 6 the File Manager checks ran in Chrome 156 (headless, throwaway profile,
real mouse and file-input events through the DevTools Protocol), because the Claude
in Chrome extension wasn't connected: create archive and download on a folder with
and without a dot file, extract into a new folder, a duplicate `Čáp.png`, a dot-named
upload, SVG disposition and folder delete. The driver scripts were disposable and are
not in the repository.

On November 6 the changed rename ran in the real File Manager through Claude in Chrome:
a name with a newline, pasted with Ctrl+V, reached the server and was refused with
`Neplatný název adresáře`, and an ordinary name still renamed the folder. A folder
already named with a newline showed its plain file and not its dot file. These runs
were driven by the implementer, so they aren't independent verification.

Release the manifest, lockfile, generated bundle, visual assets, template and
presenter together; install the lockfile, rebuild Nette/Latte caches and verify
served hashes plus connector behavior. URL-upload users need PHP cURL on the
web runtime. `www/temp/elfinder-build` and `www/temp/elfinder-source` are build
leftovers: they are ignored by git and must not ship.

## Rollback

Rolling back to 2.1.69 reintroduces the advisories above and is not a safe steady
state, so prefer a fix forward. This takes elFinder back to the client and connector
of the September 18 release and leaves the rest of release `c8e1d96d` in place, the
same way as any other rollback here: a new reviewed commit pushed forward, never a
force-push. No SQL is involved. It has not been rehearsed on production (`d63c65f2`).

Before the release, record the commit the deployed branch is on (`PREV`). Below,
`deploy` stands for the deployment remote and `production` for its branch.

Files that go back to `PREV`:

- `composer.json`, `composer.lock`
- `www/js/all-back.js`
- `app/AdminModule/templates/@layout.latte`
- `gulpfile.js`

Files that are removed: `www/elfinder/` and `tools/build/elfinder.cjs`.

Files that stay, whatever else is rolled back: `app/components/Utilities/ElfinderControl.php`
and `app/AdminModule/presenters/BasePresenter.php`. They protect `www/images/.htaccess`
and the CSRF checks and don't depend on the client version.

```sh
git fetch deploy
git switch -c rollback-elfinder deploy/production
git restore --source="$PREV" --staged --worktree -- composer.json composer.lock \
    www/js/all-back.js app/AdminModule/templates/@layout.latte gulpfile.js
git rm -r -- www/elfinder tools/build/elfinder.cjs
git status --short                  # exactly the files listed above
git diff --cached -- composer.lock  # only studio-42/elfinder, back to 2.1.69
git commit -m "Roll back elFinder to 2.1.69"
git push deploy rollback-elfinder:production
```

The hosting's `.deploy` then runs `composer install` and `delete www/temp/cache`. If
another change to `composer.json`, `composer.lock` or `@layout.latte` shipped after
this release, stop at `git diff --cached` and take only the elFinder parts.

Verify, with `https://<site>` as the production address:

```sh
curl -s https://<site>/js/all-back.js | grep -o 'Version 2\.1\.[0-9]*' | head -1  # Version 2.1.16
curl -s -o /dev/null -w '%{http_code}\n' https://<site>/elfinder/css/elfinder.min.css  # 404
curl -s -o /dev/null -w '%{http_code}\n' https://<site>/images/.htaccess  # 403
```

1. The hook output shows `studio-42/elfinder` installed at 2.1.69, and no error.
2. Sign in, open a page editor and click the File Manager button: it opens and lists
   the images root without `.htaccess`, and `elFinder.prototype.version` in the console
   is `2.1.16`.
3. Record the rollback commit's SHA and the results on `c8e1d96d`.

Expected after a rollback: uploading through the File Manager answers 403 again,
because the old layout uses the iframe transport, which can't send the CMS token
(`ffce4b6d`). The File Manager dialog opens under the Summernote toolbar again. Upload
through `/admin/files` still works. Restoring `@layout.latte` to `PREV` also drops the
content-hash query on `caloris-admin.js` (`caloris-admin.js?v={$adminScriptVersion}`),
so browsers may serve a cached old copy of that script until it changes, and it drops
the dialog-height clamp (`Math.min(850, $(window).height() - 20)`), so on a short
window the File Manager dialog can be dragged up out of view again. These come back
only because `@layout.latte` is one file; they are cosmetic, not security.

For the whole release, use `git revert` of its commit on the deployed branch
instead, and follow the release procedure recorded on `c8e1d96d`.
