# SQL migration files

These files are reviewed production deltas for manual execution through the authenticated Blueboard database administration interface. See [`docs/PRODUCTION_DEPLOYMENT.md`](../../../docs/PRODUCTION_DEPLOYMENT.md) for the full workflow.

Use `YYYY_MM_DD_short_description.sql`. New migrations should begin with SQL comments containing:

- purpose and linked Taskino task id;
- required application version;
- precondition query and expected result;
- verification query and expected result;
- rollback or restore plan;
- whether rerunning the file is safe.

Never edit a migration after it has been applied to production. Add a new corrective migration instead. Do not store production exports, credentials, tokens, password hashes, or personal data here.
