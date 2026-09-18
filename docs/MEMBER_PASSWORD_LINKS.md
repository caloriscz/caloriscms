# Member password setup and recovery

Task `2daa1c32`, local implementation 2026-09-16, awaiting Verification.

## Behavior

- Member creation optionally emails a password-setup link. The initial password is
  a hash of an undisclosed random value. Without mail, the account owner must use
  recovery or an administrator must send a link later.
- Only the existing `admin` account can assign a role on creation. Other member
  managers create accounts without a role; an administrator grants access later.
- Resending access instructions no longer changes the current password. It sends
  a setup/reset link to the account's stored email. No password is displayed,
  passed to callbacks or added to a redirect URL.
- Recovery gives the same visible response for sent, unknown, disabled, duplicate
  and throttled accounts. All affected forms use Nette session CSRF protection.
- A link expires after one hour (UTC), can be consumed once, and becomes invalid
  after the email or password changes. Resending after a one-minute cooldown
  replaces the prior link. An atomic conditional update prevents concurrent reuse.
- Only the token digest and expiry are stored. The secret contains 32 random bytes.
  The digest also binds the secret to the current email and password hash.
- The email URL uses a fragment; the reset page copies it into a hidden POST field
  and removes it from browser history. It is not sent in access-log query strings.
  JavaScript is required for link loading. Opening a link does not consume it.
- Credential mail uses the configured canonical site URL (HTTPS, except localhost)
  and sender. It bypasses Helpdesk templates, recipient overrides and body logging.
  Mail failures revoke the attempted token and retain the current password.
- Reset pages disable caching and referrers. Tracy hides password/token form keys.
  Existing authenticated sessions/API tokens retain their existing lifetimes; this
  task does not introduce global session revocation or an account recovery redesign.

## Rollout

Apply `app/model/migrations/2026_09_16_password_reset.sql` **before code deployment**.
It adds nullable `users.reset_token_hash` and `reset_expires_at`, is repeatable on
MySQL/MariaDB, and does not alter existing passwords. The fresh SQL dump includes
the same columns. No production migration was executed.

Confirm `site:url:base` contains the intended HTTPS site origin/base directory and
`contacts:email:hq` contains a valid sender. Do not derive the email origin from a
request Host header. Old Helpdesk password templates (IDs 4/5/6) are no longer used
by these flows. Historical mail logs are not modified by this code change.

Existing plaintext `users.activation` links are deliberately no longer accepted.
Their owners request a new link. Successful password changes clear that legacy
field as well as pending reset credentials. Rolling back to old code can restore
the old recovery behavior; retain the schema columns when rolling back.

## Verification

- `composer test`: seven passing files on PHP 7.4.33 / Tester 2.4.3. The new test
  covers issuance, hashing, expiry boundary, replay, resend, invalid/disabled and
  duplicate recipients, changed email/password/state, length bounds, canonical
  origin validation, fake-mail contents, cooldown and mail failure cleanup.
- SQL migration applied twice successfully to the local MariaDB database.
- `tests/manual/password-reset-fixture.php` creates/checks/removes one disposable
  account. `password-reset-http.py <fixture-token>` checks rendered form/headers,
  missing/wrong/other-session CSRF, mismatch/short password, invalid credential,
  successful reset redirect and rejected replay. MariaDB confirmed the new hash
  and cleared token. The reset account was removed.
- `member-onboarding-http.py <permission-fixture-prefix>` checked denied-role and
  CSRF rejection, successful no-mail creation and the rendered protected resend
  form. Cleanup confirmed an undisclosed hashed password, no token and no role.
  All created user/role fixtures were removed. No real mail was sent.
- Browser runtime reported no available browser. Human verification still needs
  the actual mail-client fragment link, JavaScript field loading, layout and
  delivery with the intended SMTP configuration. HTTP rendering/service checks
  passed; they do not substitute for that browser check.
