# Regression tests

The test suite uses the Nette Tester dependency already declared in `composer.json`.
It must remain independent of production databases, credentials, and filesystem data.

Run the suite in the local Docker environment:

```sh
docker compose exec -T app composer test
```

After installing Composer dependencies, the same suite can be run directly with:

```sh
composer test
```

The runner uses the installed PHP configuration (`-C`) so PDO SQLite, iconv and
the application's other extensions are available. The Docker image already has
these extensions. Missing PDO SQLite fails the suite instead of silently skipping
the database checks.

Coverage:

- elFinder patched URL-upload/no-cURL refusal, netmount CSRF gate and actual
  extracted-file MIME filtering; opt-in HTTP checks and vendor asset build are
  documented in `docs/ELFINDER_SECURITY_UPDATE.md`.
- Coordinated page creation, stale/localized slug edits, stable ordering and
  transactional rollback; real MariaDB concurrency is opt-in via
  `docs/PAGE_CREATION_CONCURRENCY.md`.

- Page-owned media upload/delete rollback, missing files, duplicate prevention
  and read-only diagnostics; see `docs/MEDIA_CONSISTENCY.md` for real HTTP checks.
- Non-page asset staging/SQL rollback, duplicate/path rejection, shared image
  ownership and menu cascades; see `docs/ASSET_MUTATIONS.md` and the opt-in
  `tests/manual/asset-http.py` for real multipart form checks.

- Frontend inline-edit permissions, safe writes, language columns and locked
  pages; see `docs/FRONTEND_INLINE_EDITING.md` for HTTP and browser coverage.
- Controlled HTML/AJAX errors for 400/403/404/405/410/500, safe public responses
  and allowlisted log context without a database; see `docs/ERROR_RESPONSES.md`.
  `ErrorResponsesHttpTest.phpt` additionally launches a disposable loopback PHP
  HTTP server (requires `proc_open` and local sockets). It tests real error
  dispatch both before and after output flush; CLI alone bypasses Nette's
  late-header check. It loads no application config and cleans its process/files.
  `tests/manual/error-handling-inventory.php` is a separate opt-in diagnostic for
  residual Helpdesk/console failures using fake mail and disposable state.
- Password setup/reset token lifecycle, recipient/password binding, cooldown and
  fake-mail failure behavior; see `docs/MEMBER_PASSWORD_LINKS.md` for HTTP checks.
- Helpdesk diagnostics without message retention, missing configuration/template/
  page fallback, transport/archive errors and escaped form feedback using fake
  mail only; see `docs/HELPDESK_FAILURES.md`.

- SlugRouter query parsing/building, route identity, locale/prefix/id round trips,
  base paths/ports and explicit template routing; see `docs/SLUG_ROUTER.md`.
- Admin page/media/member/settings/menu/contact/link/snippet/helpdesk/appearance
  permissions, nested signals and independent role grants; see
  `docs/ADMIN_PERMISSIONS.md`.
- CSRF token generation and rejection of unsafe methods, malformed values and
  tokens from another session. Local opt-in HTTP checks cover automatic admin
  forms, AJAX/native signals, uploads, elFinder and logout; see `docs/ADMIN_CSRF.md`.
  The opt-in `tests/manual/elfinder-csrf-http.py` additionally tests raw-body
  command substitution with a valid connector token but no CMS token, plus
  authorized POSTs and preserved GET/HEAD reads.
- API token hashes, scopes, expiration, revocation, disabled/missing users and
  last-used updates through the real authenticator.
- API write allowlist, validation, draft creation, HTML purification, slug
  collisions, page updates and unpublish-without-deletion behavior.
- Upload filename normalization and rejection of executable/browser-active files.
- Admin missing/non-numeric ids (`requireRecord()` 404), menu group name/class and
  Helpdesk subject length limits before strict MariaDB sees them, the Helpdesk
  settings redirect and no `$baseUri` left in templates: `Admin/AdminRecordChecksTest.phpt`.
- Contact category management (owner decision of 2026-11-08): edit reads and writes
  `contacts_categories`, no move into the own subtree, add/edit name checks, and a delete
  that refuses a category with contacts (they cascade in MariaDB) or subcategories:
  `Admin/ContactCategoriesTest.phpt`.
- Front search filter: no `count()` of null columns (a TypeError on PHP 8), and unknown or
  array order values fall back to the default order: `Pages/SearchFilterTest.phpt`.
- File Manager client assets live in `www/elfinder`, and nothing points the browser at a
  `/vendor/` URL (some hosts deny every such URL): `Security/PublicAssetPathsTest.phpt`.
- Pages list views (owner decision of 2026-11-08): the "Strom" view is gone, a stored
  `tree` (or unknown) view shows the list, the Pages list has no nested menu insert form,
  and the jsTree that sends menu editor mutations is bound only to the menu editor's
  `.menu-editor-tree`: `Admin/PagesListViewTest.phpt`.
- Shared HTML policy at remaining admin writers: snippet locales, link
  descriptions, menu insert/edit, maintenance messages and Helpdesk templates/
  rendered archives (fake mail only). `AdminHtmlWritesTest.phpt` covers real
  callbacks with SQLite; opt-in `admin-html-http.py` and `admin-html-menu.php`
  cover real protected forms/AJAX and translated MariaDB menu insertion.
  See `docs/ADMIN_HTML_POLICY.md` for fixtures, inventory and compatibility limits.

API tests create a separate SQLite `:memory:` database per test process with a
minimal synthetic schema. They never boot `app/bootstrap.php`, load local config,
or connect to MariaDB. This checks service behavior; MySQL-specific SQL, production
schema compatibility, and browser form/AJAX behavior still need local smoke checks.

Run one file during development:

```sh
docker compose exec -T app vendor/bin/tester tests/Api/ApiTokenAuthenticatorTest.phpt -C -s
```
