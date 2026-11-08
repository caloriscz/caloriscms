# SQL migration files

These files are reviewed production deltas for manual execution through the hosting's authenticated database administration interface. The deployment and data-change workflow is kept with the release task in Taskino.

Use `YYYY_MM_DD_short_description.sql`. New migrations should begin with SQL comments containing:

- purpose and linked Taskino task id;
- required application version;
- precondition query and expected result;
- verification query and expected result;
- rollback or restore plan;
- whether rerunning the file is safe.

Never edit a migration after it has been applied to production. Add a new corrective migration instead. Do not store production exports, credentials, tokens, password hashes, or personal data here.
