# Admin form and signal protection

Local implementation for task `ffce4b6d`, 2026-09-18. Pending user verification;
uncommitted and undeployed.

## Request boundary

`BootstrapUIForm` adds Nette protection when a POST form attaches to an admin
presenter, including nested forms, sign-in and multipart uploads. Existing
explicit protection is retained. Invalid tokens are rejected with 403 before
legacy validation callbacks can read values or perform side effects. Ordinary
field validation still runs for correctly protected submissions.

The admin base presenter requires POST and a session token for non-form signals.
The token travels in `_csrf` or the `X-CSRF-Token` header, never the query string.
Permission checks run first; a token does not grant access. Exceptions are the
Pages view/editor toggle cookie preferences and these elFinder reads:
`open`, `tree`, `parents`, `tmb`, `file`, `ls`, `size`, `dim`, `info`, `search`,
`get`, `url`. Other connector commands use the mutation guard.

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
| Members | InsertMember, EditMember, InsertContactForMember, SendLogin | memberGrid-delete; deleteContact (legacy no-op) |
| Menu | InsertMenu, EditMenu, InsertMenuMenus, MenuMenusEdit, UpdateImages | menuEditor-delete/create/rename/sort; menuList-delete; deleteImage |
| Contacts | InsertContact, EditContact, InsertCategory, EditCategory, InsertContactCategory, InsertHour | contactGrid-delete; deleteHour; deleteCategory; delete (legacy no-op) |
| Links | presenter insertForm/editForm | delete; deleteImage; deleteCategory; categoryPanel-delete/create/rename/sort |
| Snippets | InsertForm, EditForm | delete |
| Helpdesk | Helpdesk, EditContactForm, EditMailTemplate, EditHelpdeskEmailSettings | delete; deleteTemplate |
| Appearance | SavePaths, InsertCarousel, EditCarousel | carouselManager-delete/images |
| Pages | InsertForm, Editor, EditorSettings, ImageUpload, FilterForm | changeState; pageList-delete/public; editorSettings-public; fileList-deleteFile; thumbnail-delete/public |
| Settings | EditSettings, InsertLanguage, InsertCountry, InsertBlackList | install; makeDefault; toggle; toggleCountry; blackList-delete |
| Files / media / pictures | DropUpload, DropZone, EditFileForm, ImageEditForm, EditPictureForm | Files:delete; imageBrowser-delete/setMain; thumbnail-delete/public; all elFinder non-read commands |
| Explicit exceptions | GET Search / AdvancedSearch; public FrontModule forms | Pages:view and editor-toggle preferences; elFinder read allowlist above |

No active admin POST form family in this inventory remains unprotected. This
inventory establishes the shared boundary; the HTTP checks below exercise a
representative set of real writes, not every listed handler's business logic.

- Remaining delete links in Menu, Contacts, Links, Snippets, Helpdesk, Members
  and carousel controls use native POST forms, including without JavaScript.
- Menu/Links trees and carousel ordering use encoded AJAX POST requests. Signal
  arguments remain in the URL for the legacy presenter handlers; tokens remain
  in the POST body. Request failures display an error.
- elFinder uses POST requests and the CMS token header. An `open` event forwards
  the connector's own response token as `X-elFinder-CSRF`; this supports the
  checked-in older client with the installed newer connector. If that token
  expires, reopen the dialog to obtain a fresh token before retrying the action.
- elFinder early session closing is disabled: its close/reopen otherwise replaces
  cookie settings and loses the login on local HTTP. Nette retains session
  ownership through the connector command.
- Hand-authored scripts and template integration changed; generated bundles
  were not edited. No dependency or schema changes are required.
- Legacy no-op Contacts category deletion and Members contact deletion handlers
  remain no-ops. Dormant Pricelist JavaScript has no corresponding presenter.
  This task changes request protection, not those business behaviors.

## Verification

PHP 7.4.33: nine Nette Tester files pass; 173 PHP files lint clean. JavaScript
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
python tests/manual/inline-editing-http.py permtest-1234abcd
docker compose exec -T app php tests/manual/admin-permissions-fixture.php cleanup permtest-1234abcd
docker compose exec -T app composer test
```

Always run account cleanup even if a check fails. HTTP scripts clean their own
disposable content in `finally`; they are opt-in local integration checks.

The in-app browser was unavailable. Human Verification still needs native delete
confirmation, jsTree drag/create/rename, carousel ordering, Dropzone upload,
elFinder selection/mutation and inline title/snippet focus/blur/error feedback.
HTTP checks do not establish visual browser behavior. Broader elFinder traversal
and filesystem failure recovery remain separately tracked tasks.
