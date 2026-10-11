# Core Memory

## Project
- **Name**: Northline Supply
- **Purpose**: Shopify Embedded Application for Northline Supply merchant workflows and catalog management.
- **Current objective**: Initial application baseline and memory brain initialization.

## Current State
- **Active feature**: Template embedded app routes (`app._index.jsx`, `app.additional.jsx`).
- **Current development phase**: Foundation & Architecture Setup.
- **Important blockers**: None.

## Technology
- **Frontend**: React 18, React Router 7 (`@shopify/shopify-app-react-router`), Shopify Polaris (`@shopify/polaris-types`), Vite.
- **Backend**: Node.js, React Router server adapter (`@react-router/serve`), Shopify Admin GraphQL API (2027-01 webhook subscriptions).
- **Database**: SQLite via Prisma ORM (`@prisma/client`, `prisma`).
- **Infrastructure**: Embedded Shopify Admin app, Vite bundler, local dev tunnel via Shopify CLI (`shopify app dev`).

## Architecture
- **Key architectural decisions**:
  - Embedded Shopify Admin architecture with App Bridge React.
  - Prisma SQLite session storage (`@shopify/shopify-app-session-storage-prisma`).
  - Mandatory Shopify AI Toolkit usage for platform and API work per repo rules.
- **Important modules**:
  - `app/routes`: React Router application routes and embedded Shopify entry points.
  - `prisma/schema.prisma`: Session storage and persistent database entities.
  - `shopify.app.toml`: App configuration, access scopes (`write_products,write_metaobjects,write_metaobject_definitions`), and webhook subscriptions.

## Permanent Constraints
- **Authority**: Repository code is always authoritative over memory notes. Obsidian records intent, history, and learnings; code records current reality.
- **Security & Privacy**: Never store secrets, tokens, credentials, or private keys in Obsidian or commit history.
- **Tooling Rule**: Use Shopify AI Toolkit for Shopify API and platform work; do not add ad-hoc tooling to this repo.

## Important User Preferences
- **Execution Style**: Ponytail principles — minimal complete diffs, no unnecessary abstractions, concise reporting.
- **Memory Protocol**: Silent extraction, progressive refinement, targeted token-efficient recall, no verbatim chat dumps.

## Critical Decisions
- [[ADR_001_SHOPIFY_REACT_ROUTER_EMBEDDED_STACK]]

## Active Problems
- None active.

## Spec References
- PRD: [[PRD]] — Active / v1.0
- TRD: [[TRD]] — Active / v1.0
- App Flow: [[APP_FLOW]] — Active / v1.0
- Design Brief: [[DESIGN_BRIEF]] — Active / v1.0
- Database Schema: [[DATABASE_SCHEMA]] — Active / v1.0
- Implementation Plan: [[IMPLEMENTATION_PLAN]] — Active / v1.0

## Important Memory References
- [[OVERVIEW]]
- [[CURRENT_STATE]]
