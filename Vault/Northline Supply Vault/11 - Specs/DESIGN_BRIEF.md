---
type: spec
doc: DESIGN_BRIEF
status: Active
version: 1.0
last_updated: 2026-10-11
confidence: VERIFIED
related: [[APP_FLOW]]
---

# Design Brief

## 1. Design Direction
- System: Shopify Polaris Design System.
- Look & Feel: Clean, official Shopify Admin native aesthetic. Fits seamlessly into merchant's daily workspace.
- Target Context: Desktop-first Shopify Admin dashboard (responsive for tablet / mobile admin).

## 2. Color System
| Token | Hex / Value | Usage |
|-------|-------------|-------|
| `--p-color-bg-surface` | `#FFFFFF` | Primary card background |
| `--p-color-text` | `#202223` | Primary text |
| `--p-color-text-subdued` | `#6D7175` | Secondary / helper text |
| `--p-color-border` | `#E1E3E5` | Card and divider borders |
| `--p-color-bg-fill-success` | `#008060` | Action badges and success feedback |

## 3. Typography
- Font Family: Inter, -apple-system, BlinkMacSystemFont, "San Francisco", "Segoe UI", Roboto, sans-serif.
- Weights: Regular (400), Medium (500), Semibold (600), Bold (700).

## 4. Spacing, Layout, and Grid
- Layout: Polaris `Page`, `Layout`, `Layout.Section`, `BlockStack`, `InlineStack`.
- Spacing Tokens: `space-200` (8px), `space-400` (16px), `space-600` (24px).

## 5. UI Components
| Component | Variants | States | Notes |
|-----------|----------|--------|-------|
| `Page` | Full-width, narrow | Loaded | Shell wrapper with page title and primary actions |
| `Card` | Default, rounded | Default, hovered | Content container |
| `Button` | Primary, Plain, Tone Critical | Default, Loading, Disabled | Interactive triggers |
| `Banner` | Status: Info, Success, Warning, Critical | Dismissible | System feedback and announcements |

## 6. Iconography & Imagery
- Shopify Polaris icons SVG set via `@shopify/polaris-icons`.

## 7. Motion & Interaction
- Standard Polaris transitions (150ms ease-in-out for button states and accordion disclosures).

## 8. Accessibility Requirements
- Full WCAG 2.1 AA compliance as mandated by Shopify App Store Review guidelines.
- Visible keyboard focus rings and proper ARIA landmarks.

## 9. Key Screens
- Screen `SCR-002` (Home): Top welcome banner, primary call-to-action cards, resource links.
- Screen `SCR-003` (Additional Page): Informational card layout with Polaris text blocks.

## Change Log
| Date | Change | Old → New | Reason |
|------|--------|-----------|--------|
| 2026-10-11 | Initial baseline spec | None → v1.0 | Project initialization from scaffold |
