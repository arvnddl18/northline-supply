# System Architecture

## Overview
Northline Supply operates as an embedded Shopify Admin application.

## Key Layers
1. **Client / Admin Iframe**: Rendered within Shopify Admin using `@shopify/app-bridge-react` and Shopify Polaris components.
2. **Server Runtime**: Node.js running `@react-router/serve` to handle SSR and API routes.
3. **Authentication Layer**: `@shopify/shopify-app-react-router` handles OAuth handshake, token generation, and HMAC request validation.
4. **Data Persistence**: Prisma Client connected to SQLite (`prisma/dev.sqlite`) storing session credentials.
5. **Platform Services**: Shopify GraphQL Admin API for interacting with products, inventory, and metaobjects.
