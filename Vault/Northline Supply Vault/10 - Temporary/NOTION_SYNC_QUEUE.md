# Notion Sync Queue

This file buffers pending Notion Kanban board updates when the Notion MCP server is unreachable, disconnected, or unauthenticated. Once the connection is re-established, the agent replays these actions and clears the queue.

*No pending actions. All task state synchronized.*
