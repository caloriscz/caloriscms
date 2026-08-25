# CalorisCMS Codex Guide

This is the primary working guide for agents in this repository. It is intentionally
small and operational: use Taskino for planning/state, then work inside the existing
legacy Nette CMS structure.

## Project Overview

CalorisCMS is a content management system running on Nette Framework.

- Backend: PHP `>= 7.4`, Nette `^3.1`, Tracy, Contributte packages, HTMLPurifier, elFinder.
- Frontend/admin assets: jQuery, Bootstrap 4, Summernote, Dropzone, jsTree, Flatpickr,
  Nette forms/ajax, and Gulp concatenation.
- Local runtime: Docker Compose with PHP/Apache app, MariaDB 10.6, optional phpMyAdmin,
  and a reset-admin helper.

## Repository Layout

- `app/`: Nette application code, config, model/database dump, presenters, templates, and modules.
- `www/`: public document root and browser assets.
- `docker/`: local Docker image, database import, config, and helper scripts.
- `vendor/`, `node_modules/`, `log/`, and `temp/`: dependency/runtime folders. Do not treat
  them as source work unless the user explicitly asks.
- `.codex/config.toml`: local Codex MCP config with a secret Taskino API key. This file must stay uncommitted.
- `.mcp.json`: local Claude-style MCP config with the same Taskino API key. This file must stay uncommitted.

## Local Commands

Run from the repo root unless a specific subdirectory is required.

- Start local stack: `docker compose build` then `docker compose up -d`
- Open frontend: `http://localhost:8090`
- Open admin: `http://localhost:8090/admin`
- Reset local admin password: `docker compose --profile tools run --rm reset-admin`
- Optional phpMyAdmin: `docker compose --profile tools up -d phpmyadmin`, then open `http://localhost:8081`
- Reset all local data: `docker compose down -v` then `docker compose up -d`
- Install PHP dependencies without Docker only when needed: `composer install`
- Install JS dependencies without Docker only when needed: `npm install`
- Build admin JS bundle: `npx gulp scripts-a`
- Build frontend JS bundle: `npx gulp scripts-f`

The seeded local admin can be reset to username `admin` and password `admin` using the
reset command above. Do not copy production credentials into local config, docs, tasks, or logs.

## Taskino MCP Requirement

Taskino is the planning and activity backend for this project. Use the MCP server from the
repo root so local MCP config is loaded (`.codex/config.toml` for Codex, `.mcp.json` for Claude-style clients).

- Taskino project: `Caloris CMS`
- Taskino agent username: `agent-caloris`
- Taskino display name: `Caloris`
- Taskino app expected locally: `http://localhost:8081`

Codex must have a project-local `.codex/config.toml` entry named `taskino` using the
`taskino-mcp` Docker image, `TASKINO_BASE_URL=http://host.docker.internal:8081`,
`TASKINO_PROJECT=Caloris CMS`, and a `TASKINO_API_KEY` generated for the active
`agent-caloris` / `Caloris` account. Claude-style clients use `.mcp.json` with the same
values. These files contain secrets and must remain ignored/uncommitted.

At the start of work, verify Taskino MCP is available (for Codex, use `/mcp` or the active
MCP tool list) and that the authenticated account is `agent-caloris` / `Caloris`. If
Taskino MCP is not available, or if the configured key resolves to another account, report
that and do not replace it with ad hoc local planning files.

Use Taskino as the source of truth for:

- tasks, issues, and ideas
- task notes and progress updates
- project documents and linked references
- project chat and agent coordination
- agent activity visibility
- version/developer-journal context when those tools are available

## Taskino Workflow

Before changing production code or project docs:

1. Find the `Caloris CMS` project in Taskino.
2. Find or create a Taskino task/issue/idea for the work.
3. Assign work to the `agent-caloris` identity in notes or fields when supported.
4. Move the task to `in_progress` when implementation starts.
5. Add timestamped notes with what changed, commands run, blockers, and verification results.
6. Use Taskino documents for durable design notes, API notes, migration notes, or user-facing docs.
7. Use project chat for lightweight agent-to-agent coordination.
8. Do not mark a task `done` unless the user explicitly confirms the work is accepted.

When a Taskino tool supports author or identity fields, use `agent-caloris` / `Caloris`.
When it only accepts plain note text, include `agent-caloris` in the note body.

## Engineering Rules

- Preserve Nette conventions already used in the surrounding code.
- Keep PHP changes compatible with PHP `>= 7.4` unless the project is explicitly upgraded.
- Avoid direct edits to generated dependency folders and runtime folders.
- Do not edit `app/config/config.local.neon` as a source change; use tracked config templates or
  Docker config when changing local setup intentionally.
- Keep SQL dump changes deliberate and documented in Taskino because they affect fresh local data.
- Prefer small, targeted changes over broad rewrites in legacy presenters, templates, and models.
- Do not hand-edit concatenated vendor bundles in `www/js/all-back.js` or `www/js/all-front.js`;
  update inputs and rerun the appropriate Gulp task when a bundle change is required.
- Treat user/runtime data in `log/`, `temp/`, and local database volumes as disposable local state
  unless the user explicitly asks to inspect them.

## Verification

Run the narrowest practical verification for the change:

- For Docker/runtime changes, bring the stack up with `docker compose up -d` and check the app/admin.
- For asset changes, rerun the relevant Gulp task and verify the affected page.
- For PHP behavior, prefer a focused browser/manual check through the local Docker stack. If tests are
  added or discovered, run the focused Nette Tester command and document it in Taskino.
- If a command cannot run because dependencies, Docker, or network access are unavailable, record that
  explicitly in the Taskino task note and in the final response.
