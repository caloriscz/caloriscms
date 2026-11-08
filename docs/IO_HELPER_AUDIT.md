# IO helper audit

Task `a55f5357`, verified locally on 2026-09-16 with PHP 7.4.33.

No first-party caller of `IO::rename()` or `IO::folderSize()` was found in `app/`
or `tests/` before adding the diagnostic below. Both reported defects are real,
but neither was established as an active application failure.

Run the disposable diagnostic:

```sh
docker compose exec -T app php tests/manual/io-helper-audit.php
```

| Case | Observed behavior |
| --- | --- |
| Existing source, missing destination | No move; source remains |
| Existing source and destination | No move; destination preserved |
| Both paths missing | No operation |
| Missing source, existing destination | PHP warning; destination preserved |
| Empty directory size | 0 bytes |
| One three-byte file | 3 bytes |
| Nested directory with five-byte file | Undefined `App\Model\folderSize()` error |

The script uses random temporary paths, captures the expected warning/error and
removes only its fixtures in `finally`. It is a diagnostic, outside the `.phpt`
regression suite; its successful exit is not evidence that the helpers work.

## Decision

Keep the unused implementation unchanged, as scoped by the task. Do not introduce
callers until these defects are fixed. Before adopting rename, define missing-path
and overwrite behavior explicitly (default: preserve an existing destination).
Before adopting folderSize, fix qualified recursion and define symbolic-link,
unreadable-directory and large-file behavior. Then replace this characterization
with behavioral regression tests. No broad IO rewrite is justified by this audit.
