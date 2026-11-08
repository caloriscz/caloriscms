# Admin permission checks

The shared admin presenter checks the current database user and role on every
request, before actions, component signals and form submissions run. Anonymous
visitors retain the login redirect. Missing/disabled users, missing roles and
roles without `sign = 1` receive 403. Existing sessions do not retain revoked
database permissions.

This pass protects these areas using existing `users_roles` columns:

| Area | Required permissions |
| --- | --- |
| Pages, including create/edit/delete/publish | `pages` |
| Page image actions and image components | `pages` and `pictures` |
| Page file actions and file components | `pages` and `media` |
| Files image manager | `pictures` |
| Files file-detail/edit component | `media` (and the current action's permission) |
| Members, including password reset/send-login form | `members` |
| Settings, including blacklist mutations | `settings` |
| Menu, including tree signals and image forms | `menu` |
| Contacts, categories and opening hours | `contacts` |
| Links and Snippets, including categories and nested forms | `pages` |
| Helpdesk messages, email settings and templates | `helpdesk` |
| Appearance paths and carousel | `appearance` |
| Shared editor signals on other admin presenters | `pages` |
| elFinder media root | `media` |
| elFinder images root | `pictures` |

`AdminPermissions::requireRequest()` consumes Nette's resolved action/signal,
so changing the form URL, using POST `_do`, or sending an AJAX `do` cannot bypass
the checks. elFinder exposes only the roots the current role permits. A CSRF
token is still required by previously protected handlers; it never grants a
permission by itself. No username-based administrator override was added.

Verification: `composer test` includes a role matrix. The local HTTP smoke used
temporary denied, page-only, media-only and fully permitted users, checked GET,
POST, nested form/signal and AJAX requests, inspected elFinder roots, and removed
its fixtures. Existing CSRF rejection and valid-toggle/restore smoke checks passed.

## Remaining sections pass (2026-09-17)

Task `e4f26e2f` extends the same startup guard to the six sections above. Links
and Snippets use `pages`: both manage published content, the role schema has no
separate grants, and snippet creation already used `pages`. No schema change is
needed. Menu image uploads and carousel editing use their section grants; the
separate Files manager retains its pictures/media grants.

Sign-in/recovery remain public. Homepage and the current user's Profile require
an active user with `sign`, but no section grant. Shared editor signals still
require `pages` even on those presenters. Existing Settings and role-management
restrictions remain in place. No username bypass was introduced. In particular,
the historical seeded Admin role had `appearance = 0`, so enforcement denies
Appearance until its data is corrected. The role screen is read-only; it cannot
grant this flag. On September 23 the owner approved restoring this role's access.
The fresh-install seed now has `appearance = 1`. Existing databases need the
targeted migration below; other roles and all request permission checks remain.

### Approved Admin-role correction (September 23, not yet applied on Blueboard)

Use authenticated database administration with the normal current-backup procedure.
First inspect the intended role and assignment count:

```sql
SELECT id, title, appearance FROM users_roles WHERE id = 1;
SELECT COUNT(*) AS assigned_users FROM users WHERE users_roles_id = 1;
```

If row 1 is exactly the default `Admin` role, run
`app/model/migrations/2026_09_23_admin_appearance.sql`. It changes only
`appearance=0` to `1` for that ID and exact, case-sensitive name. Re-running is a
no-op. A missing/renamed role is deliberately unchanged; inspect its actual
identity instead of granting every role named Admin. Recheck the flag and the
unchanged assignment count, then open `/admin/appearance/` and
`/admin/appearance/carousel/` as a user on that role. Existing sessions reload
permissions on their next request. A role with `appearance=0` must still get 403.

The SQL and seed correction are prepared locally; production application and
browser retest remain pending. No runtime migration runner or username override
is introduced. `tests/manual/admin-appearance-migration.php` tests the exact SQL
against a connection-local MariaDB temporary table without altering real roles.

Run locally with `docker compose exec -T app php tests/manual/admin-appearance-migration.php`.
September 23 result: intended role updated once; rerun, other roles, renamed
role (including lowercase `admin`) and absent role all unchanged as expected.
Focused permission test passes. Migration SHA-256:
`400059386c1318c20b829f58415a4a840d6e1851994a4f444aaf71f4022018c8`.

Navigation visibility is not the authorization boundary: legacy links can remain
visible to denied users. Startup rejects direct URLs, POST `_do`, AJAX and nested
forms before they can mutate data. New sections must be added to the shared map.

Local HTTP verification uses `tests/manual/admin-permissions-fixture.php` for
temporary accounts with independent grants and `remaining-permissions-data.php`
for isolated records. Run the HTTP script's `denied` phase, the data fixture's
`unchanged` check, the HTTP `allowed` phase, then the data fixture's `deleted`
check. Both HTTP phases take a prefix and the comma-separated IDs returned by
data creation. Always run `cleanup` for both fixture scripts afterwards.

Result: all six sections passed direct GET, POST, AJAX and nested-form rejection
with unrelated or missing grants. Database checks confirmed unchanged records
after denials and deletion of each test record with its matching independent
grant. Profile remained available. All fixtures were removed. Eight regression
files pass on PHP 7.4.33; 169 PHP files lint clean. No production changes were made.

Frontend inline editing (`96775eb4`) and broader admin CSRF protection (`ffce4b6d`)
are now implemented locally for Verification. See `FRONTEND_INLINE_EDITING.md`
and `ADMIN_CSRF.md`. Permission smoke requests for allowed mutations must include
the rendered session token; authorization and CSRF checks both apply.
