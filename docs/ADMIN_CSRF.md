# Admin form and signal protection

Task `ffce4b6d`: original implementation deployed on September 18; the September 23
verification corrections and the October 21 elFinder upload fix below remain local
and undeployed (release `c8e1d96d`). October 21 browser checks are recorded under
Verification.

## Request boundary

`BootstrapUIForm` adds Nette protection when a POST form attaches to an admin
presenter, including nested forms, sign-in and multipart uploads. Existing
explicit protection is retained. Invalid tokens are rejected with 403 before
legacy validation callbacks can read values or perform side effects. Ordinary
field validation still runs for correctly protected submissions.

The admin base presenter requires POST and a session token for non-form signals.
The token travels in `_csrf` or the `X-CSRF-Token` header, never the query string.
Permission checks run first; a token does not grant access. Exceptions are the
Pages view/editor toggle cookie preferences and body-free GET/HEAD elFinder reads:
`open`, `tree`, `parents`, `tmb`, `file`, `ls`, `size`, `dim`, `info`, `search`,
`get`, `url`, and `zipdl` with `download=1` (the client's plain link to a prepared
ZIP). Any other `download` value is not exempt: elFinder prepares a ZIP when it is
empty, as with `download=0`, and that needs the token.
The decision reads the same PHP superglobals elFinder's connector executes from -
`$_SERVER['REQUEST_METHOD']`, `$_GET['cmd']` and `$_GET['download']` - and additionally
requires an empty `$_POST`, empty `$_FILES` and an empty raw body. It does not use
Nette's parsed request, because the two can disagree: a second space-prefixed query key
(`cmd=open&%20cmd=zipdl`, which PHP folds back onto `cmd`), a NUL-truncated key
(`download%00x`), and `X-HTTP-Method-Override: GET` (which Nette honours but the
connector ignores) each made Nette see a read while the connector would run a
mutation. Reading the connector's own view closes that gap, and rejecting a nonempty
raw body keeps the connector's post-truncation reparse from substituting a command.
Every other elFinder request requires the CMS token; the connector's own CSRF check
remains an independent second guard.

Admin and frontend admin-bar logout now submit protected POST forms. Signed-in
pages with tokens use private, no-store responses. Public frontend forms remain
outside the admin form scope; inline editing has its own guarded path described
in [FRONTEND_INLINE_EDITING.md](FRONTEND_INLINE_EDITING.md).

## Controls and compatibility

### Coverage matrix

All forms listed below use the shared admin POST-form boundary. Signal names
are relative to their presenter or component; existing guards remain active.

| Area | Protected form families | Protected signals/actions |
| --- | --- | --- |
| Sign / Profile | SignIn, LostPass, ResetPass, Edit, ChangePassword | Sign:out |
| Members | InsertMember, EditMember, InsertContactForMember, SendLogin | memberGrid-delete; deleteContact |
| Menu | InsertMenu, EditMenu, InsertMenuMenus, MenuMenusEdit, UpdateImages | menuEditor-delete/create/rename/sort; menuList-delete; deleteImage |
| Contacts | InsertContact, EditContact, InsertCategory, EditCategory, InsertContactCategory, InsertHour | contactGrid-delete; deleteHour; deleteCategory |
| Links | presenter insertForm/editForm | delete; deleteImage; categoryPanel-delete/create/rename/sort |
| Snippets | InsertForm, EditForm | delete |
| Helpdesk | EditMailTemplate, EditHelpdeskEmailSettings | delete; deleteTemplate |
| Appearance | SavePaths, InsertCarousel, EditCarousel | carouselManager-delete/images |
| Pages | InsertForm, Editor, EditorSettings, FilterForm | pageList-delete/public; editorSettings-public; fileList-deleteFile |
| Settings | EditSettings, InsertLanguage, InsertCountry, InsertBlackList | install; makeDefault; toggle; toggleCountry; blackList-delete |
| Files / media / pictures | DropUpload, DropZone, EditFileForm, ImageEditForm, EditPictureForm | Files:delete; imageBrowser-delete/setMain; all elFinder non-read commands |
| Explicit exceptions | GET Search / AdvancedSearch; public FrontModule forms, including the public Helpdesk contact form (`HelpdeskControl`, rendered by the frontend contact/homepage components) | Pages:view and editor-toggle preferences; body-free GET/HEAD elFinder read allowlist above |

No active admin POST form family in this inventory remains unprotected. This
inventory establishes the shared boundary; the HTTP checks below exercise a
representative set of real writes, not every listed handler's business logic.

Not reachable, so not listed above (corrected October 21): `PageThumbControl`
(`thumbnail-delete/public`, both copies) is never attached to a presenter;
`EditContactFormControl` and `ImageUploadControl` are never created; and
`PagesPresenter::handleChangeState()` is `protected`, so Nette never dispatches it
as a signal. If one is wired up later it falls under the same boundary, but it
must be added here and checked then.

- Remaining delete links in Menu, Contacts, Links, Snippets, Helpdesk, Members
  and carousel controls use native POST forms, including without JavaScript.
- Menu/Links trees and carousel ordering use encoded AJAX POST requests. Signal
  arguments remain in the URL for the legacy presenter handlers; tokens remain
  in the POST body. Request failures display an error.
- elFinder uses POST requests and the CMS token header. The bundled 2.1.70 client
  stores the connector's own token from the init `open` response and sends it as
  `X-elFinder-CSRF`; the layout's `open` handler does the same as a fallback. If
  that token expires, reopen the dialog to obtain a fresh token before retrying.
- Both tokens travel only in headers, and the connector reads its token only from
  `X-elFinder-CSRF`. elFinder's iframe upload transport cannot send headers, so
  both guards reject it. The admin dialog therefore sets `dragUploadAllow: true`,
  which selects the XHR upload transport for the context-menu upload and also
  enables drag-and-drop upload. Until October 21 it was `false`, and File Manager
  uploads failed with 403 in a real browser. The September 18 deployment has the
  same setting, so production uploads are most likely affected too (not checked
  on production). The CMS token is deliberately not added to `customData`:
  that would not satisfy the connector and would put the token into GET
  file/thumbnail URLs.
- elFinder early session closing is disabled: its close/reopen otherwise replaces
  cookie settings and loses the login on local HTTP. Nette retains session
  ownership through the connector command.
- Hand-authored scripts and template integration changed; generated bundles
  were not edited. No dependency or schema changes are required.
- The admin layout versions `caloris-admin.js` with the first 16 SHA-256
  characters of its actual file contents, so a new deployment's URL matches the
  served file and browsers with a cached old copy request the new asset. The
  `?v=` query lives in `@layout.latte`, so an elFinder rollback that restores that
  file to `PREV` (see the Rollback section of `ELFINDER_SECURITY_UPDATE.md`) drops
  the query and a cached old `caloris-admin.js` may be served until the file changes;
  this is cosmetic, and the rolled-back layout is otherwise self-consistent.
- Legacy no-op Contacts category deletion and Members contact deletion handlers
  remain no-ops. Dormant Pricelist JavaScript has no corresponding presenter.
  This task changes request protection, not those business behaviors.

### Expired or invalid form token (owner decision, not implemented)

Current behavior, kept as the owner decided: an admin POST form whose token is
missing, wrong or from an expired session throws `ForbiddenRequestException` in
`BootstrapUIForm` before validation. The user sees the generic 403 page with only
a homepage link. Nette's `The form expired. Please try again.` message never
shows, and what was typed is lost (Back reloads the form empty). A left-open
sign-in page hits this after the session expires.

Proposed narrow correction, only if the owner agrees:

- Scope: rendered admin POST forms whose request already passed the presenter's
  sign-in and permission checks, the sign-in form included. Non-form signals,
  AJAX, Dropzone and elFinder keep the plain 403.
- In `BootstrapUIForm`'s `onAnchor`, instead of throwing: add the form error
  `The form expired. Please try again.`, keep the 403 status, and let the
  presenter re-render the same form with a fresh token.
- Re-render the submitted non-secret values. Password fields and the old token
  are not repopulated.
- Nothing is written. With an error already present, Nette's
  `Form::fireEvents()` skips `validate()` (so no legacy `onValidate` callback
  runs) and never calls `onSuccess` or a button's `onClick`. Only `onError`
  runs, and no admin form uses it today.
- Checks needed: the existing invalid-token HTTP cases must still show no
  writes and assert the in-form error, plus a browser check that a sign-in or
  edit form left open past session expiry can be resubmitted after one retry.

## Verification

October 21 follow-up: in Chrome on the local stack, a PNG uploaded through the
page editor's File Manager context menu lands in a `fix_` folder in the images
root, and uploading the same name again creates `name-1.png` without overwriting
it (both files byte-identical to the source). Drag upload works too: a PNG
dropped onto the file list uploads over XHR. The drop was a `drop` event
dispatched in the page with the file attached, because the automation cannot
drag files from the desktop. Every connector POST returned 200.
`elfinder-csrf-http.py` now also posts an upload the way each client
transport sends it: the header-less iframe form is 403 with nothing written; the
XHR form with both headers uploads, and a repeated name becomes `fix-1.png` with
both files byte-identical. `ElfinderSecurityTest.phpt` asserts that the admin
dialog keeps `dragUploadAllow: true` and the CMS header; it fails on the old
`false`. The existing `admin-csrf-http.py`, `csrf-upload-http.py` and
`elfinder-security-http.py` suites still pass. All 17 Tester files pass and 196
PHP/PHPT files lint clean on PHP 7.4.33, 8.0.30 and 8.2.33.

September 23 follow-up: `elfinder-csrf-http.py` supplies a valid connector token
but no CMS token, then sends a query read command with a raw-body mutation large
enough to trigger the connector's alternate parser. POST and GET are both 403
with no directory created. Plain POST reads without/with a wrong CMS token are
403; valid POST reads, GET/HEAD reads, and authorized mkdir/rm succeed. The existing
16-signal/seven-form `admin-csrf-http.py` pass also succeeds, including encoded
tree rename, rendered deletion and logout. All temporary fixtures are removed.
These are HTTP checks, not browser event/visual checks.

The following records the original September 18 implementation checks:

Historical September 18 counts (superseded by the totals above): nine Nette
Tester files passed and 173 PHP files linted clean on PHP 7.4.33. JavaScript
syntax and Latte compilation were checked. Local HTTP checks use isolated
records/accounts and no mail delivery:

- `admin-csrf-http.py`: 16 signal paths reject GET and missing, wrong and
  other-session tokens without database changes; seven real edit forms reject
  invalid tokens and accept valid saves. Encoded tree rename, rendered native
  delete form and protected logout work.
- `csrf-upload-http.py`: missing/wrong tokens cannot write an uploaded image;
  valid multipart upload preserves its bytes. elFinder open, rejected mkdir,
  valid mkdir and remove work in the same signed-in session.
- `inline-editing-http.py`: anonymous, denied, disabled, missing/revoked role,
  unsafe methods and bad tokens leave content unchanged; valid AJAX saves and
  special characters work.
- Member onboarding, password reset and error-response HTTP checks still pass
  with the protected form login helper. Disposable rows/files/accounts are
  removed after checking.

Reproduce with a fresh eight-hex-digit prefix and the local Docker stack:

```sh
docker compose exec -T app php tests/manual/admin-permissions-fixture.php create permtest-1234abcd
python tests/manual/admin-csrf-http.py permtest-1234abcd
python tests/manual/csrf-upload-http.py permtest-1234abcd
python tests/manual/elfinder-csrf-http.py permtest-1234abcd
python tests/manual/inline-editing-http.py permtest-1234abcd
docker compose exec -T app php tests/manual/admin-permissions-fixture.php cleanup permtest-1234abcd
docker compose exec -T app composer test
```

Always run account cleanup even if a check fails. HTTP scripts clean their own
disposable content in `finally`; they are opt-in local integration checks.

Browser status: no browser was available on September 18 or 23. The October 20
independent verification passed native delete confirmation, jsTree
create/rename/delete, logout, Dropzone and carousel ordering in Chrome, and
found the File Manager upload failure fixed on October 21 (see above). Inline
title/snippet behavior is covered in
[FRONTEND_INLINE_EDITING.md](FRONTEND_INLINE_EDITING.md). Broader elFinder
traversal and filesystem failure recovery remain separately tracked tasks.
