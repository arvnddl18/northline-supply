# ADR 001: Shopify React Router Embedded Stack

## Context
The application needs to provide a responsive, native-feeling merchant tool integrated into the Shopify Admin.

## Decision
Use Shopify's official React Router template (`@shopify/shopify-app-react-router` with Vite and Prisma) configured as an embedded app (`embedded = true`).

## Status
Active

## Date
2026-10-11

## Rationale
- Native integration with Shopify App Bridge.
- Full server-side rendering and client-side transitions via React Router.
- Built-in session token management and automatic token exchange.
