# PHP and Nette upgrade readiness

Task `32c1e30a`, assessed 2026-09-17; verification corrections on 2026-09-23
against source `bf21b1b` plus the local follow-up changes. Corrections are not deployed.

## Decision

The current lockfile and tested code can be evaluated on PHP 8.2 as a short bridge.
They are **not ready for an unchanged deployment on PHP 8.3, 8.4 or 8.5** because
locked dependencies explicitly exclude those runtimes. Prefer evaluating PHP 8.4
as a staged target after dependency updates; consider 8.5 once those packages and
hosting are confirmed. This is an assessment, not production upgrade approval.

PHP 8.2 security support ends 2026-12-31; 8.3 ends 2027-12-31, 8.4 ends 2028-12-31
and 8.5 ends 2029-12-31. PHP 8.4 and 8.5 are in active support on the assessment
date. See the [official PHP support schedule](https://www.php.net/supported-versions.php).

Support dates rechecked on 2026-09-23. PHP 8.0 reached end of life on 2023-11-26,
and PHP 7.4 on 2022-11-28 ([official unsupported branches](https://www.php.net/eol.php)).
Passing compatibility tests on those versions does not make them supported targets.

## Compatibility matrix

September 26 follow-up: the targeted elFinder lockfile update, non-page asset
work and Helpdesk diagnostics pass all **16** current tests on PHP7.4.33,
8.0.30 and 8.2.33; **194** PHP/PHPT files lint clean on PHP7.4. Current lock SHA-256
is `43BFB8D75F51A111F1C7AF8E77DB2CA4AFAD31FAF6B08BCAE38BEA82AB876D6B`.
Only elFinder changed (2.1.69 to2.1.70); older lock hashes/results below are
historical. No production runtime upgrade or new browser acceptance is claimed.

September 24 local follow-up: after error-response task `97fc2a75` and HTML writer
task `51270fae`, all **13** current Tester files pass and **189** PHP/PHPT files
lint clean on PHP 7.4.33/8.0.30/8.2.33. Local PHP7.4 protected HTML forms, menu
insertion and existing admin CSRF/inline HTTP regressions pass. See
`docs/ADMIN_HTML_POLICY.md` and `docs/ERROR_RESPONSES.md`. The September 23 matrix
below remains a dated snapshot; these later CLI results do not establish
production PHP or browser acceptance.

| Runtime | Locked dependencies | Local lint/tests | HTTP/browser evidence |
| --- | --- | --- | --- |
| PHP 7.4.33 | Current configured local runtime | 183 files lint clean; 11 Tester files pass on September 23 | Historical local HTTP baseline; September 23 admin CSRF/elFinder/inline AJAX checks pass; browser unavailable |
| PHP 8.0.30 | No PHP constraint prohibitions | 183 files lint clean; 11 Tester files pass on September 23 | CLI probe only; the hosting's deploy hook reports PHP 8.0, without patch version; web runtime unconfirmed |
| PHP 8.2.33 | No PHP constraint prohibitions | 183 files lint clean; 11 Tester files pass on September 23 after correcting the template fixture | September 17 HTTP: home/login/reset/documents/gallery/sitemap 200, API 401. No current authenticated browser/editing/upload pass |
| PHP 8.3 | Five locked packages prohibit it | Not run with an incompatible dependency set | Not tested |
| PHP 8.4 | Fourteen locked packages prohibit it | Not run with an incompatible dependency set | Not tested |
| PHP 8.5 | Fifteen locked packages prohibit it | Not run with an incompatible dependency set | Not tested |

Counts include first-party PHP and `.phpt` files in `app/` and `tests/` at the
final September 23 follow-up, including the new manual Admin-role migration check.
All three runtimes were rerun after the CSRF/assets/role changes. The old seven-file/165-file results describe the
September 17 snapshot only. The later committed suite reproduced one failure on
PHP 8.2: its error-response fixture assigned `basePath` on the generic Latte
`Template`. Using `DefaultTemplate`, as the application does, removes that dynamic
property deprecation. No application error handling or dependency was changed by
this fixture correction. PHP 8.2 CLI base image digest used for the existing probe:
`sha256:350410175b65a56c924871b1d7af6bd7a5aa27a9cd94fee5236fc54967ab5955`.

PHP 8.0.30 probe image ID:
`sha256:d366f1b47def0576f08a174ec571379860d2e385f6a98aef5195e3867ceadf83`.
Its final official image uses Debian Bullseye; current indexed ICU package URLs
returned 404 during the first build. The probe Dockerfile uses a dated Debian
2023-11-01 snapshot for Bullseye only. Snapshot validity expiry is disabled for
that frozen index; package signature verification remains enabled. This obsolete
image is solely a disposable, network-disabled test environment, not a deployment
recommendation. Other base distributions keep their normal repositories.

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
docker build --build-arg PHP_VERSION=8.0.30 -t caloriscms-readiness:8.0 docker/readiness
docker build --build-arg PHP_VERSION=8.2.33 -t caloriscms-readiness:8.2 docker/readiness
docker run --rm --network none -v E:/projects/apps/caloriscms/caloriscms-reconcile:/work:ro -v caloriscms-reconcile_composer_vendor:/work/vendor:ro -w /work caloriscms-readiness:8.0 php vendor/bin/tester tests -C -s
docker run --rm --network none -v E:/projects/apps/caloriscms/caloriscms-reconcile:/work:ro -w /work caloriscms-readiness:8.0 php tests/manual/php-readiness-lint.php
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

1. Complete the hosting record in task `d63c65f2`: exact web and CLI PHP
   versions, extensions and available upgrade targets. Deployment task `a4565e37`
   records Composer running on PHP 8.0 on September 18 (again noted September 20).
   That is CLI evidence, not proof of the web server's PHP version or extensions.
   The release and forward-revert procedure are recorded on the release task
   `c8e1d96d`; target selection still needs web/runtime confirmation.
2. Accept pending security changes, expand password/media/XML and MariaDB
   regressions (`30a38cf0`), and exercise authenticated browser editing/uploads
   (`b5a1c3a8`). A passing CLI suite is a useful starting point only. Browser
   discovery on September 23 again returned no available browsers; no new browser
   result is claimed. The separate error-response regression `97fc2a75` was fixed
   locally on September 24 with an actual HTTP late-header regression. All twelve
   current tests pass on PHP 7.4.33/8.0.30/8.2.33; acceptance and release remain
   separate. The earlier DefaultTemplate fixture correction alone did not fix it.
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
