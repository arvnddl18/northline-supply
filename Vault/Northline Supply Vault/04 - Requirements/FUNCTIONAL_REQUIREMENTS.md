# Functional Requirements

## Authentication & Authorization
- Seamless single-click installation for merchant stores.
- Offline access token persistence for background processing.
- Required access scopes: `write_products`, `write_metaobjects`, `write_metaobject_definitions`.

## UI Workflows
- Embedded Admin UI inside Shopify Admin.
- Polaris-compliant forms, banners, and layout primitives.

## Lifecycle Management
- Automatic cleanup on `app/uninstalled` webhook.
- Automatic scope refresh on `app/scopes_update` webhook.
