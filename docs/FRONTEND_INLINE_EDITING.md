# Frontend inline editing

Task `96775eb4`, implemented September 17 and deployed September 18.
The September 23 asset-cache correction and the October 21 title/emoji
validation fix remain local and undeployed (release `c8e1d96d`). Browser checks
were run on October 20 (independent verification) and October 21; see below.

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

Corrected October 21: a title is trimmed of Unicode whitespace at both ends and
rejected (400) if nothing remains after removing all Unicode whitespace (`\p{Z}`)
and format characters (`\p{Cf}`). Before, `trim()` missed the NBSP that Chrome
inserts for a typed space, so a blank title was saved and reported "Saved". The
same applied to zero-width spaces and bidi controls such as U+202E. U+2028/U+2029
are rejected like other line breaks. Titles containing characters above U+FFFF
(emoji) are rejected with 400 before the write. The columns are utf8mb3, so these
used to fail in SQL with an unhandled 500; the client shows its normal "Not saved…"
status. Snippets are HTML, so since November 5 they store such a character as a
numeric reference (`&#128512;`), as the admin editors do; see
`docs/ADMIN_HTML_POLICY.md`. Moving the schema to utf8mb4 remains an owner decision.
Non-default locales require an enabled language and an installed target column;
they never silently overwrite default content. Token-bearing pages are private,
no-store. The client reports failed saves and uses the sanitized response on success.

`www/js/all-front-custom.js` is hand-authored and is not a Gulp output; generated
vendor bundles were not changed. Legacy SnippetControl remains available to themes;
the current document `[snippet="id"]` expansion remains read-only.

The frontend layout now versions `all-front-custom.js` using the first 16 SHA-256
characters of the actual asset contents. A deployment or rollback therefore
requests the matching client code even when the old URL remains cached. The
feature is retained under the existing global `pages` permission; no new page
ownership policy or editable snippet UI was introduced.

## Verification

October 21 (current), Chrome on the local stack with a disposable editor and an
editable `fix-inline` page:

- select all, type a space (Chrome inserts U+00A0) and click outside → POST 400,
  "Not saved…";
- the same with a zero-width space and Enter → 400;
- appending an emoji and pressing Enter → 400, "Not saved…", no 500 and nothing
  in `exception.log`;
- after these attempts `pages.title` was byte-for-byte unchanged;
- a normal edit (`fix_inline A&B žluťoučký`) with Enter → 200, "Saved", and the
  title and `<title>` persist after reload;
- typing and then Escape restores the text, blurs the title and sends no
  request.

`InlineEditingTest.phpt` covers space, NBSP, U+200B, U+202E, U+2003, a mixed
blank title, U+2028 inside a title and emoji titles (all 400), emoji snippets (a
numeric reference, typed or as an entity), plus trimming of surrounding NBSP.
`inline-editing-http.py` posts the blank and emoji cases against MariaDB. All 17
Tester files pass and 196 PHP/PHPT files lint clean on PHP 7.4.33, 8.0.30 and 8.2.33.

The October 20 independent verification had already passed logged-out,
admin-bar-off, focus/blur, failure status and retained-cache behavior in Chrome.

The following HTTP/test results are historical implementation evidence from
September (no browser was available then).

September 23 local follow-up: inline AJAX no-write/safe-save/special-character
checks rerun successfully with fixture cleanup. Frontend and admin JS URLs were
fetched over HTTP and their version values matched the served asset hashes;
Latte lint passes. This establishes asset delivery and server behavior, not
keyboard interaction or cache refresh in an actual browser.

- `composer test` (September; now 16 files): nine files passed, including isolated write/no-write cases,
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
