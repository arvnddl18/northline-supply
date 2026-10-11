# ADR 004: Notion MCP Kanban Board Integration

## Context
Previously, task workflow and backlog management were tracked locally using the Obsidian Kanban markdown plugin (`DEVELOPMENT_KANBAN.md`) synchronized via `scripts/sync-kanban.js`. While this kept markdown in the vault, it required manual or script-driven markdown file edits and lacked real-time interactive development buddy capabilities across client and AI workflows.

## Decision
Adopt Notion as the primary live Kanban board, connected directly to Antigravity through the Notion MCP server (`notion-mcp-server`), operating under the Development Buddy Mode protocol defined in Section 37 of [[obsidian-memory]].

The source of truth separation is strictly defined:
- **Task status & live progress**: Notion Kanban board (via Notion MCP)
- **Planned scope & task specifications**: PRD & [[IMPLEMENTATION_PLAN]] in `11 - Specs/`
- **Current reality**: Repository source code
- **Learnings & architectural decisions**: Obsidian memory vault
- **Next actions**: Current user instruction

`DEVELOPMENT_KANBAN.md` is preserved as a historical snapshot and backup.

## Status
Active

## Date
2026-10-12

## Rationale
- Enables two-way, real-time board interaction directly by the AI agent during development without manual file editing.
- Preserves the Obsidian vault as the persistent memory, blueprint, and cognitive context layer without bloating it with high-churn card position edits.
- Maintains strict security: Notion integration tokens are kept exclusively in MCP configuration files or environment variables, never committed to git or stored in vault markdown.
- Supports lightweight offline resilience via `10 - Temporary/NOTION_SYNC_QUEUE.md`.

## Related
- [[CORE_MEMORY]]
- [[BACKLOG_RULES]]
- [[CURRENT_STATE]]
- [[IMPLEMENTATION_PLAN]]
