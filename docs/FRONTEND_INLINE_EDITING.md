# Frontend inline editing

Task `96775eb4`, locally verified 2026-09-17, pending human Verification.

The feature is retained. PageTitleControl and the reusable SnippetControl expose
editable content only to the current active database user with `sign` and `pages`,
with the site and member admin-bar preferences enabled. Page titles additionally
respect `pages.editable`. Permissions are reloaded each request, including for
existing sessions whose user was disabled or role revoked. No username bypass or
new ownership restriction was introduced: the existing `pages` grant is global.

Both handlers require POST and the existing session mutation token, read record
IDs/content only from POST, and return JSON containing the normalized saved value.
The client now sends Nette's `_do` signal and an encoded data object to the current
path, preserving locale and special characters. SlugRouter still drops query `do`.
Titles reject markup/control characters and enforce the 250-character DB limit.
Snippets use the existing editor/API HTMLPurifier policy and a bounded input size.
Non-default locales require an enabled language and an installed target column;
they never silently overwrite default content. Token-bearing pages are private,
no-store. The client reports failed saves and uses the sanitized response on success.

`www/js/all-front-custom.js` is hand-authored and is not a Gulp output; generated
vendor bundles were not changed. Legacy SnippetControl remains available to themes;
the current document `[snippet="id"]` expansion remains read-only.

## Verification

- `composer test`: nine files pass, including isolated write/no-write cases,
  translated columns, plain titles, HTML filtering and locked pages.
- `python tests/manual/inline-editing-http.py <permission-fixture-prefix>` uses
  disposable rows and existing `admin-permissions-fixture.php` accounts. It checks
  anonymous, denied, disabled, missing-role, revoked-role, GET, missing/wrong/other
  session token attempts leave data unchanged; real AJAX saves preserve special
  characters and sanitize snippet HTML; invalid titles do not overwrite content.
- The script restores its temporary admin-bar setting and deletes its rows in
  `finally`; separately clean up the permission account fixture.
- JavaScript syntax check passes. No browser connection is available, so actual
  focus/blur, keyboard and save-status interactions remain a human review check.
- New classes require rebuilding the production-mode RobotLoader cache when
  deploying. Local cache was refreshed. No production changes were made.
