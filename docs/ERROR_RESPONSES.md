# Controlled error responses

Task `97fc2a75`, local verification 2026-09-17.

The 403/404 templates previously inherited the normal frontend layout without a
`content` block. Denied requests could log a second Latte exception. Error startup
also loaded site settings from the database, making database failures fragile.

The existing ErrorPresenter now uses the framework presenter directly, a dedicated
static layout and no page/settings queries. Its status-specific views and original
500 exception logging remain. It preserves HTTP status and returns
`{"error":true,"code":403}` (with the corresponding code) for AJAX errors. It
accepts forwarded errors for unsupported HTTP methods without generating another
405. Error responses use `Cache-Control: no-store`.

Access messages identify status, original presenter/action/signal and numeric
record/page IDs. Added context uses a strict allowlist; it does not include request
bodies, tokens, passwords or arbitrary exception messages. Existing Tracy source
URL metadata and unexpected-exception logging are retained; this does not add a
global redaction layer for third-party logging.

## Verification

- `tests/Security/ErrorResponsesTest.phpt` runs the real presenter/templates for
  400/403/404/405/410/500, HTML and AJAX, without app configuration or a database.
  It checks status, safe public bodies and log-context filtering.
- `tests/manual/error-responses-http.py <permission-fixture-prefix>` checked real
  denied 403, missing 404, unsupported OPTIONS 405 with Allow header, AJAX JSON,
  no-store, expected operation details and no new exception-log entries.
- Homepage/login/documents/gallery returned 200; eight Tester files pass.
- Disposable local role accounts were removed; no production data was changed.

This constructor/inheritance change requires clearing generated Nette DI and
presenter caches as well as Latte cache on deployment. The existing
`utilities:cache-clear` command only clears Latte. Local caches under
`www/temp/cache/nette.configurator` and `nette.application` were rebuilt; sessions
and user files were preserved. Code remains uncommitted and undeployed.
