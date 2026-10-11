---
type: spec
doc: TRD
status: Active
version: 1.0
last_updated: 2026-10-11
confidence: VERIFIED
related: [[PRD]], [[DATABASE_SCHEMA]], [[IMPLEMENTATION_PLAN]]
---

# Technical Requirements Document

## 1. Tech Stack
- Frontend: React 18, React Router 7 (`@shopify/shopify-app-react-router`), App Bridge React (`@shopify/app-bridge-react`), Polaris Types.
- Backend: React Router Node runtime adapter (`@react-router/node`, `@react-router/serve`).
- Database: SQLite via Prisma ORM (`@prisma/client` v6.16.3).
- Tooling: Shopify CLI (`shopify app dev`), Vite (`vite` v6+), TypeScript.
- Platform: Shopify GraphQL Admin API (version 2027-01).

## 2. Architecture
- Architecture style: Modular monolithic embedded application.
- Entry point: `app/routes/app.jsx` initializes App Bridge, Polar navigation, and authenticated boundaries.
- Data flow: Merchant client → Shopify Admin iframe → React Router Loaders/Actions → `@shopify/shopify-app-react-router` authentication verification → Prisma session query → Shopify GraphQL Admin API.

## 3. APIs
| Endpoint | Method | Purpose | Auth | Request | Response |
|----------|--------|---------|------|---------|----------|
| `/api/auth` | GET | Shopify OAuth callback | Shopify HMAC | OAuth code/shop | Redirect to app |
| `/webhooks/app/uninstalled` | POST | Cleanup on app uninstall | Shopify Webhook HMAC | Webhook payload | 200 OK |
| `/webhooks/app/scopes_update` | POST | Refresh updated scopes | Shopify Webhook HMAC | Webhook payload | 200 OK |

## 4. Integrations
| Service | Purpose | Auth method | Config location (never the secret) |
|---------|---------|-------------|------------------------------------|
| Shopify Admin API | Catalog & Metaobjects | OAuth Access Token | `shopify.app.toml`, `.env` |
| Shopify Webhooks | Lifecycle notifications | HMAC Signature validation | `shopify.app.toml` |

## 5. Security
- Session tokens validated via `@shopify/shopify-app-react-router`.
- Secrets stored in `.env` (never committed).
- Webhook verification via Shopify cryptographic headers.

## 6. Performance & Scalability Targets
- Embedded frame TTFB < 500ms.
- Session lookup overhead < 5ms via local SQLite session table.

## 7. Technical Constraints & Limitations
- Single SQLite file (`prisma/dev.sqlite`) intended for local development; requires PostgreSQL/MySQL for multi-tenant production.
- Shopify API and platform work must utilize Shopify AI Toolkit per workspace rule.

## 8. Environments (dev / staging / prod)
- Dev: Local development via `npm run dev` and Shopify CLI tunnels.
- Prod: Dockerfile / container deployment with `react-router-serve ./build/server/index.js`.

## 9. Testing Strategy
- Static typing: `npm run typecheck` (`tsc --noEmit`).
- Linting: `npm run lint` (`eslint`).

## 10. Open Technical Questions
- Production database provider selection when moving beyond development SQLite.

## Change Log
| Date | Change | Old → New | Reason |
|------|--------|-----------|--------|
| 2026-10-11 | Initial baseline spec | None → v1.0 | Project initialization from scaffold |
