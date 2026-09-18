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

- Coordinated page creation, stale/localized slug edits, stable ordering and
  transactional rollback; real MariaDB concurrency is opt-in via
  `docs/PAGE_CREATION_CONCURRENCY.md`.

- Page-owned media upload/delete rollback, missing files, duplicate prevention
  and read-only diagnostics; see `docs/MEDIA_CONSISTENCY.md` for real HTTP checks.

- Frontend inline-edit permissions, safe writes, language columns and locked
  pages; see `docs/FRONTEND_INLINE_EDITING.md` for HTTP and browser coverage.
- Controlled HTML/AJAX errors for 400/403/404/405/410/500, safe public responses
  and allowlisted log context without a database; see `docs/ERROR_RESPONSES.md`.
- Password setup/reset token lifecycle, recipient/password binding, cooldown and
  fake-mail failure behavior; see `docs/MEMBER_PASSWORD_LINKS.md` for HTTP checks.

- SlugRouter query parsing/building, route identity, locale/prefix/id round trips,
  base paths/ports and explicit template routing; see `docs/SLUG_ROUTER.md`.
- Admin page/media/member/settings/menu/contact/link/snippet/helpdesk/appearance
  permissions, nested signals and independent role grants; see
  `docs/ADMIN_PERMISSIONS.md`.
- CSRF token generation and rejection of unsafe methods, malformed values and
  tokens from another session. Local opt-in HTTP checks cover automatic admin
  forms, AJAX/native signals, uploads, elFinder and logout; see `docs/ADMIN_CSRF.md`.
- API token hashes, scopes, expiration, revocation, disabled/missing users and
  last-used updates through the real authenticator.
- API write allowlist, validation, draft creation, HTML purification, slug
  collisions, page updates and unpublish-without-deletion behavior.
- Upload filename normalization and rejection of executable/browser-active files.

API tests create a separate SQLite `:memory:` database per test process with a
minimal synthetic schema. They never boot `app/bootstrap.php`, load local config,
or connect to MariaDB. This checks service behavior; MySQL-specific SQL, production
schema compatibility, and browser form/AJAX behavior still need local smoke checks.

Run one file during development:

```sh
docker compose exec -T app vendor/bin/tester tests/Api/ApiTokenAuthenticatorTest.phpt -C -s
```
