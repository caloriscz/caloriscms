# PHP and Nette upgrade readiness

Task `32c1e30a`, assessed 2026-09-17 against the uncommitted reconciliation worktree.

## Decision

The current lockfile and tested code can be evaluated on PHP 8.2 as a short bridge.
They are **not ready for an unchanged deployment on PHP 8.3, 8.4 or 8.5** because
locked dependencies explicitly exclude those runtimes. Prefer evaluating PHP 8.4
as a staged target after dependency updates; consider 8.5 once those packages and
hosting are confirmed. This is an assessment, not production upgrade approval.

PHP 8.2 security support ends 2026-12-31; 8.3 ends 2027-12-31, 8.4 ends 2028-12-31
and 8.5 ends 2029-12-31. PHP 8.4 and 8.5 are in active support on the assessment
date. See the [official PHP support schedule](https://www.php.net/supported-versions.php).

## Compatibility matrix

| Runtime | Locked dependencies | Local lint/tests | HTTP/browser evidence |
| --- | --- | --- | --- |
| PHP 7.4.33 | Current configured runtime | 165 files lint clean; seven Tester files pass | Existing local HTTP baseline and password/member checks pass; browser unavailable |
| PHP 8.2.33 | No PHP constraint prohibitions | 165 files lint clean; seven Tester files pass after installing intl | Home, login, reset form, documents, gallery, sitemap: 200; unauthenticated API: 401. No browser or authenticated editing/upload pass |
| PHP 8.3 | Five locked packages prohibit it | Not run with an incompatible dependency set | Not tested |
| PHP 8.4 | Fourteen locked packages prohibit it | Not run with an incompatible dependency set | Not tested |
| PHP 8.5 | Fifteen locked packages prohibit it | Not run with an incompatible dependency set | Not tested |

Counts include first-party PHP and `.phpt` files at lint time, before adding the
small built-in-server router fixture. PHP 8.2 CLI image digest:
`sha256:350410175b65a56c924871b1d7af6bd7a5aa27a9cd94fee5236fc54967ab5955`.

Lockfile SHA-256:
`5D3F367BCCA449C0E449C1BC3CD8AAB09A17A11A21815723BDA536295190805B`.

The first stock PHP 8.2 image run passed six files and failed API validation on a
missing-intl notice. Installing intl resolved it without application changes.
Tests use SQLite; they do not prove all MariaDB query/cascade behavior. The HTTP
probe used the local Docker MariaDB with no mutations, isolated temporary cache,
logs and sessions, a read-only source/vendor mount and the explicit local config.
The temporary server was stopped and removed. No exception log was produced in
the final HTTP pass. PHP's built-in server initially returned 404 for sitemap;
forwarding virtual routes through `php-readiness-router.php` resolved that harness
limitation. This server does not verify Apache `.htaccess` behavior.

## Exact lockfile blockers

For PHP 8.3, these require `<8.3`:

- `nette/bootstrap v3.1.4`
- `nette/caching v3.1.4`
- `nette/php-generator v3.6.9`
- `tracy/tracy v2.9.8`
- `nette/tester v2.4.3`

PHP 8.4 additionally conflicts with `latte/latte v2.11.7`, `nette/database v3.1.9`,
`nette/di v3.1.10`, `nette/forms v3.1.15`, `nette/http v3.2.4`, `nette/mail v3.1.11`,
`nette/schema v1.2.5`, `nette/security v3.1.8` (ranges ending at 8.3), and
`nette/utils v3.2.10` (`<8.4`). PHP 8.5 also conflicts with `nette/neon v3.3.4`
(range ending at 8.4). These are constraint results, not vulnerability findings.

The [Nette compatibility table](https://nette.org/en/maintenance) describes the
latest patch releases in each series, not these exact locked versions. The root
`php >=7.4` constraint alone does not establish compatibility. Follow individual
package migration guides and upgrade incrementally, as described in the
[Nette upgrade guide](https://doc.nette.org/en/migrations).

Latte 2.11.7's installed linter completed successfully for `app/`. This does not
prove Latte 3 compatibility, especially for DB-stored Helpdesk templates that the
file scan cannot inspect. The [Latte migration guide](https://latte.nette.org/en/cookbook/migration-from-latte2)
recommends checking with 2.11 first and linting again under Latte 3. Review custom
BootstrapUIForm rendering, Contributte translation integration and application
template inheritance when changing these packages. Nette Database 3.2 changes
some returned MySQL value types; see its [upgrade notes](https://doc.nette.org/en/database/upgrading).

## Reproduce the probes

From the reconciliation repository:

```powershell
docker compose exec -T app composer prohibits php 8.2.0 --locked
docker compose exec -T app composer prohibits php 8.3.0 --locked
docker compose exec -T app composer prohibits php 8.4.0 --locked
docker compose exec -T app composer prohibits php 8.5.0 --locked
docker compose exec -T app php vendor/bin/latte-lint app
docker compose exec -T app php tests/manual/php-readiness-lint.php
docker build -t caloriscms-readiness:8.2 docker/readiness
docker run --rm --network none -v E:/projects/apps/caloriscms/caloriscms-reconcile:/work:ro -v caloriscms-reconcile_composer_vendor:/work/vendor:ro -w /work caloriscms-readiness:8.2 php vendor/bin/tester tests -C -s
docker run --rm --network none -v E:/projects/apps/caloriscms/caloriscms-reconcile:/work:ro -w /work caloriscms-readiness:8.2 php tests/manual/php-readiness-lint.php
```

The optional HTTP probe uses the same image with `php -S 0.0.0.0:8080 -t www
tests/manual/php-readiness-router.php`, port `127.0.0.1:8092`, the existing local
Docker network, an explicit read-only Docker config override and tmpfs mounts at
`/work/www/temp` and `/work/www/log`. Never run it against production configuration.
The probe Dockerfile is separate from the PHP 7.4 application image and does not
install the full production extension set (GD, mbstring, mysqli and zip remain
necessary for corresponding workflows).

## Staged upgrade and release gates

1. Record Blueboard's actual web and CLI PHP versions, available targets, enabled
   extensions, Composer runtime, document root and rollback method in task
   `d63c65f2`. These remain unverified; there is no evidence-based production target
   until that record exists.
2. Accept pending security changes, expand password/media/XML and MariaDB
   regressions (`30a38cf0`), and exercise authenticated browser editing/uploads
   (`b5a1c3a8`). A passing seven-file suite is a useful starting point only.
3. In a separate upgrade branch/environment, resolve the five PHP 8.3 blockers
   first through compatible package releases. Keep a reviewed lockfile and run
   tests, template lint, full-extension HTTP/browser checks and deprecation
   collection. Never bypass Composer platform requirements to claim readiness.
4. Resolve the PHP 8.4 package bounds, following individual Nette/Latte/Contributte
   upgrade notes. Treat Latte 3 as its own reviewed change if required. Recheck
   API validation, dates/nullable relations, forms/AJAX, file manager and SMTP.
5. Select the production target only after hosting confirmation and rehearsal.
   Deploy dependencies/code/cache in the verified order with a rollback plan.
   The password-link SQL migration is a separate prerequisite for that code;
   this readiness task does not authorize production schema/runtime changes.

No dependencies, composer.lock, the application Dockerfile, production runtime,
or production data were changed by this assessment. The resulting assessment is
ready for verification; the production upgrade remains gated by the items above.
