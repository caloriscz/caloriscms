# Controlled error responses

Task `97fc2a75`, updated local verification 2026-09-24.

The September 17 baseline was deployed September 18. The September 24
late-header correction below is local and awaits release via `c8e1d96d`.

## Late-header regression (September 24)

Reproduced through a real PHP HTTP server and Nette Application error dispatch:
after a response prefix was flushed, ErrorPresenter called `setCode()` and
raised a second `Nette\InvalidStateException`. The original failure was not
logged by the presenter because logging came after header changes. Ordinary CLI
tests missed this: the installed Nette Response bypasses `checkHeaders()` in CLI.
The September 23 DefaultTemplate fixture fix did not address this behavior.

ErrorPresenter now logs the original failure before attempting response changes.
If headers are already sent, it logs a warning with the sent status and allowlisted
operation/record context, then returns a VoidResponse. It does not append HTML or
JSON to an existing body. Merely guarding `setCode()` would be insufficient:
JsonResponse also sets Content-Type when sending the body.

Once output is sent, neither the HTTP status, cache headers nor prior bytes can be
replaced. The original partial response therefore remains as sent, potentially
with a success status; the warning exposes this limit for diagnosis. The fix
prevents recursive failure and preserves evidence, not recovery of a broken
upstream stream. When headers are writable, existing status, no-store and
HTML/AJAX error behavior is preserved.

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

- September 24: `ErrorResponsesHttpTest.phpt` starts a disposable loopback PHP
  HTTP server with output buffering disabled. Real Nette Application dispatch
  covers 400/403/404/405/410/500, ordinary/AJAX, before/after actual output flush
  (24 cases). It checks original exception logging, no secondary error, unchanged
  already-sent status/content/cache/body, and normal no-store/JSON behavior.
  No app configuration, database or SMTP is loaded. Server/files are cleaned.
- The pre-fix HTTP reproduction failed with
  `already-sent-prefixSECONDARY-ERROR:Nette\InvalidStateException`; it passes
  after the fix. All twelve regression files pass on PHP 7.4.33, 8.0.30 and 8.2.33.
- September 24 local CMS HTTP checks passed: 403/404 HTML and AJAX JSON, 405 with
  Allow, no-store, contextual logs, normal home/login/documents/gallery pages,
  and no new secondary exception logs. Disposable account integrity/cleanup pass.
- `tests/Security/ErrorResponsesTest.phpt` runs the real presenter/templates for
  400/403/404/405/410/500, HTML and AJAX, without app configuration or a database.
  It checks status, safe public bodies and log-context filtering.
- `tests/manual/error-responses-http.py <permission-fixture-prefix>` checked real
  denied 403, missing 404, unsupported OPTIONS 405 with Allow header, AJAX JSON,
  no-store, expected operation details and no new exception-log entries.
- Historical September 17 run: eight Tester files passed.
- Disposable local role accounts were removed; no production data was changed.

This constructor/inheritance change requires clearing generated Nette DI and
presenter caches as well as Latte cache on deployment. The existing
`utilities:cache-clear` command only clears Latte. Local caches under
`www/temp/cache/nette.configurator` and `nette.application` were rebuilt; sessions
and user files were preserved. The September 24 correction changes no constructor
or DI registration; deliver it with the pending release and follow its cache plan.

## Residual failure-path inventory (September 24)

September 26 follow-up: `26a2cd83` now records redacted Helpdesk diagnostics and
controls nullable configuration/form fallback; see `docs/HELPDESK_FAILURES.md`.
`bc1cecb6` now gives standalone uploads checked staging, duplicate409 and
controlled exceptions/JSON success; see `docs/ASSET_MUTATIONS.md`. The table below
records the original findings. Console diagnostics remain open in `01c4ce6c`.

`php tests/manual/error-handling-inventory.php` is an opt-in diagnostic using
in-memory SQLite, a fake mailer and temporary files, not a regression asserting
that defects must remain. It never loads application configuration or sends mail.

| Path | Current evidence / disposition |
| --- | --- |
| Frontend member setup | Historical swallowed catch is gone. Current member/role lookups have null checks and propagate DB errors to ErrorPresenter. No equivalent silent catch remains. |
| Helpdesk mail | Fake SendException with message-body logging disabled returns false and produces zero diagnostic log entries. Separate task `26a2cd83` owns safe transport/configuration diagnostics and nullable form fallback review. Password-link mail is separate. |
| Cache / thumbnail console | Wrong-type disposable cache path and missing pictures table return exit 1; cache does not falsely report success in this probe. Output lacks explicit command/record context; no diagnostic log entry. Separate task `01c4ce6c` owns contextual diagnostics. |
| Menu / link tree / carousel | Direct echo/exit success responses remain for existing clients. No local broad response-contract rewrite; exceptions before exit propagate to central errors. Broader normal/invalid browser behavior remains `b5a1c3a8`. |
| Standalone file upload | Explicit empty 400 and exit failure paths remain outside page-owned MediaStorage. Existing non-page asset task `bc1cecb6` owns failure consistency/feedback. |
| Frontend maintenance / IP / cookie gates | Direct output/exit are intentional content gates, not swallowed exception handlers; unchanged. |
| API and page-owned media | API validation catches produce controlled errors; media rollback/recovery is already bounded by MediaStorage and separate recovery task `807a6cc8`. |

Added log context remains allowlisted. Existing Tracy exception/URL metadata is
unchanged; this is not a global redaction system or a claim to repair every
legacy failure in one task.
