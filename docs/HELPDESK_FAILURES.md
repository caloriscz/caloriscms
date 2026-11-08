# Helpdesk failure diagnostics

Task `26a2cd83`, September 26, 2026. Local implementation ready for Verification;
production deployment and browser acceptance remain separate.

## Reproduction and behavior

The original `error-handling-inventory.php` probe used a fake failing mailer and
message retention disabled: `send()` returned false with zero diagnostic entries.
The corrected probe returns false with one safe diagnostic. Cache/thumbnail
command gaps are still separate task `01c4ce6c`; their diagnostic counts remain
zero and the inventory now measures each command independently.

Helpdesk logs failures independently of optional message-body retention. Records
contain only `event=helpdesk.failure`, allowlisted operation/category and numeric
Helpdesk/template IDs. They omit recipients, names, message/subject content,
credentials, raw SMTP/SQL exception messages and exception traces. Operations
distinguish configuration lookup, rendering, message construction, transport,
archive retention, validation and return-page resolution.

Missing Helpdesk/template/sender data, malformed templates and transport
Exceptions/Errors produce controlled false results. Successful delivery remains
true if subsequent archive persistence fails; that failure gets its own record
without suggesting the user resend an already delivered message. The existing
opt-in message archive still retains message content as before and sanitizes its
browser HTML; diagnostic redaction does not change that retention setting.

The public form validates name/email/message and blacklist rules without nullable
page dereferences or `die()`. Invalid forms cannot reach successful submission;
the configured page remains the redirect target, with `/` as a safe fallback if
configuration/page data is absent. Control characters and backslashes are
removed from the stored slug before it gets a single leading slash, so the
return path can never become `//host`. Success/failure messages retain their
Czech wording. Helpdesk-tagged presenter flashes are rendered as escaped text by
`HelpdeskFeedback.latte`, including on the fallback page. Other flashes are not
rendered by this partial. Password-link mail remains unchanged and separate.

Corrected October 21: until then the feedback was never shown. Nette's
`Presenter::redirectUrl()` does not append the flash key (`_fid`); only
`redirect()`/`link()` do. The return page therefore never loaded the flash. The
control now redirects to the return path with `?_fid=<key>` (same internal
target, no new redirect source). Nette keeps a flash for 30 seconds, so the
frontend presenter drops the flash section once a Helpdesk flash has been
rendered: a reload shows nothing. The earlier unit test replaced `flashMessage()`
and `redirectUrl()` in a fake presenter and could not notice the missing key.

## Validation

`HelpdeskFailuresTest.phpt` uses in-memory SQLite, a capture logger, fake mailer
and real form callbacks. It verifies success, failed transport with body logging
disabled, thrown Error, missing Helpdesk/template/sender/page, malformed Latte,
failed archive SQL after successful delivery, invalid fields, blacklist rejection,
redirect/flash outcomes and escaped feedback. Its presenter still has no session,
so its `flashMessage()` stand-in generates the flash key the way the real
`getFlashSession()` does; the test asserts that the redirect carries it
(`/contact?_fid=…`) and that tab, CR/LF and backslash slugs cannot point at
another host. Synthetic secrets must never appear in diagnostics; IDs and
operation/category inputs are allowlisted. No real mail is sent and no
SMTP/production configuration is loaded.

`tests/manual/helpdesk-feedback-http.py` is the real HTTP check: logged out, it
posts the rendered `/kontakt` form, follows the redirect, expects the alert once
(`E-mail nebyl odeslán` for a valid submit, since local Docker cannot deliver
mail, and `Vyplňte jméno` for an empty name), expects no alert on reload, and
checks that new log lines contain none of the submitted fields.

October 21: all 16 Tester files pass and 195 PHP/PHPT files lint clean on PHP
7.4.33, 8.0.30 and 8.2.33; the HTTP check passes. In Chrome on `/kontakt`, a
valid submit and an empty-name submit each land on `/?_fid=…` with the red
alert, a reload removes it, and the logs only gain redacted
`helpdesk.failure` lines. Historical September 26 counts: 16 tests, 194 files.

```sh
docker compose exec -T app vendor/bin/tester tests/Security/HelpdeskFailuresTest.phpt -C -s
docker compose exec -T app php tests/manual/error-handling-inventory.php
docker compose exec -T app composer test
```

No browser is connected. Human Verification should check the contact form's
message placement and redirects. Real mail delivery is not claimed by fake-mail
tests. Ship the model/control/feedback partial/layout together and rebuild Latte
caches in release `c8e1d96d`; no database migration is required.
