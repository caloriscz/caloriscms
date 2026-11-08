-- Owner-approved restoration of Appearance for the default seeded Admin role.
-- First inspect users_roles id=1 and confirm it is the intended Admin role.
-- A renamed/replaced role is deliberately left unchanged; do not widen this
-- predicate automatically. See docs/ADMIN_PERMISSIONS.md for preflight/retest.
-- Re-runnable. Changes only appearance; no user/session or other grant changes.
UPDATE users_roles
SET appearance = 1
WHERE id = 1 AND BINARY title = 'Admin' AND appearance = 0;
