# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

The project's agent instructions (stack, commands, critical gotchas, architecture, conventions) live in `AGENTS.md` and are imported here so there is a single source of truth. Update `AGENTS.md`, not this file, when those change.

@AGENTS.md

## Additional notes
- `README.md` is the stock Laravel README and has no project-specific information.
- `tigre_teve_db` at the repo root is a tracked SQLite database file. It is not the app's configured DB (MySQL via `.env`); do not point tests at it, since `RefreshDatabase` would wipe it.
- The `frontend-design` skill referenced in `AGENTS.md` lives outside the repo (`~/.agents/skills/frontend-design/SKILL.md`); if it is missing on this machine, say so rather than skipping the design process silently.
