<style>
    /* -------------------------------------------------------------
     * Northwind Ceramics — Artisan Design Architecture Overrides
     * WCAG 2.2 AA Compliant & Emil Kowalski Micro-Physics
     * ------------------------------------------------------------- */

    /* Hide Shopify App Bridge custom elements from normal rendering */
    ui-nav-menu,
    ui-title-bar,
    ui-nav-menu:not(:defined),
    ui-title-bar:not(:defined) {
        display: none !important;
    }

    /* Tab Panels Navigation Switching */
    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
        animation: page-rise 380ms cubic-bezier(.2, .7, .2, 1) both;
    }

    @keyframes page-rise {
        from {
            opacity: 0;
            transform: translateY(6px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Focus Indicators (WCAG 2.2 AA SC 2.4.11 & SC 2.4.13) */
    :focus-visible {
        outline: 2px solid var(--terracotta) !important;
        outline-offset: 2px !important;
    }

    /* Accessible Skip Link */
    .skip-link {
        position: fixed;
        top: 8px;
        left: 8px;
        z-index: 99999;
        transform: translateY(-180%);
        padding: 12px 18px;
        border-radius: 8px;
        color: #ffffff;
        background: var(--ink);
        font-weight: 600;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        transition: transform 160ms ease-out;
    }

    .skip-link:focus {
        transform: translateY(0);
    }

    /* Universal Reduced Motion Accessibility (WCAG SC 2.3.3) */
    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }
        .button:active, .btn:active, .quick-action:active {
            transform: none !important;
        }
    }

    /* High-Contrast Badges */
    .status-sage, .badge-success {
        color: #31563A !important;
        background: #EBF2EC !important;
        border-color: #D1E1D3 !important;
    }

    .status-ochre, .badge-warning {
        color: #784C07 !important;
        background: #FBF4E8 !important;
        border-color: #EFDFBF !important;
    }

    .status-red, .badge-danger {
        color: #9B2C22 !important;
        background: #FDEBEA !important;
        border-color: #F3CFCB !important;
    }

    /* Cashflow bar chart animation */
    @keyframes bar-rise {
        from {
            transform: scaleY(0);
            opacity: 0;
        }
        to {
            transform: scaleY(1);
            opacity: 1;
        }
    }

    /* Slide-Over Drawer Container Mechanics */
    .nc-drawer-container {
        position: fixed;
        inset: 0;
        z-index: 9000;
        display: flex;
        justify-content: flex-end;
    }

    .nc-drawer-scrim {
        position: fixed;
        inset: 0;
        background: rgba(28, 25, 23, 0.45);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        opacity: 0;
        transition: opacity 280ms cubic-bezier(.2, .7, .2, 1);
    }

    .nc-drawer-container.is-open .nc-drawer-scrim {
        opacity: 1;
    }

    .nc-drawer-sheet {
        position: relative;
        z-index: 9001;
        width: min(580px, 100vw);
        height: 100vh;
        background: #ffffff;
        box-shadow: -14px 0 40px rgba(28, 25, 23, 0.16);
        border-left: 1px solid var(--clay-200);
        display: flex;
        flex-direction: column;
        transform: translateX(100%);
        transition: transform 320ms cubic-bezier(.32, .72, 0, 1);
    }

    .nc-drawer-container.is-open .nc-drawer-sheet {
        transform: translateX(0);
    }

    .nc-drawer-header {
        padding: 22px 28px;
        border-bottom: 1px solid var(--line);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fffdfa;
    }

    .nc-drawer-title-group h3 {
        margin: 4px 0 0;
        font: 600 20px/1.2 var(--serif);
        color: var(--ink);
    }

    .nc-drawer-body {
        padding: 28px;
        overflow-y: auto;
        flex: 1;
    }

    .nc-drawer-footer {
        padding: 18px 28px;
        border-top: 1px solid var(--line);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        background: var(--clay-50);
    }

    .nc-btn-close {
        background: none;
        border: none;
        font-size: 24px;
        line-height: 1;
        color: var(--ink-soft);
        cursor: pointer;
        padding: 6px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 140ms ease, background 140ms ease;
    }

    .nc-btn-close:hover {
        color: var(--ink);
        background: var(--clay-100);
    }

    /* Centered Accessible Dialog Modals */
    .nc-modal-scrim {
        position: fixed;
        inset: 0;
        z-index: 9500;
        background: rgba(28, 25, 23, 0.5);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        opacity: 0;
        transition: opacity 240ms cubic-bezier(.2, .7, .2, 1);
    }

    .nc-modal-scrim.is-open {
        opacity: 1;
    }

    .nc-modal-box,
    .nc-modal-dialog {
        position: relative;
        z-index: 9501;
        width: 100%;
        max-width: 540px;
        max-height: 90vh;
        overflow-y: auto;
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid var(--clay-200);
        box-shadow: 0 16px 44px -8px rgba(28, 25, 23, 0.22);
        display: flex;
        flex-direction: column;
        transform: scale(0.96) translateY(8px);
        transition: transform 260ms cubic-bezier(.2, .7, .2, 1);
    }

    .nc-modal-scrim.is-open .nc-modal-box,
    .nc-modal-scrim.is-open .nc-modal-dialog {
        transform: scale(1) translateY(0);
    }

    .nc-modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--line);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fffdfa;
    }

    .nc-modal-header h3 {
        margin: 0;
        font: 600 19px/1.2 var(--serif);
        color: var(--ink);
    }

    .nc-modal-body {
        padding: 24px;
        flex: 1;
    }

    .nc-modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--line);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        background: var(--clay-50);
        border-radius: 0 0 14px 14px;
    }

    /* Responsive Breakpoints & Mobile Collapse */
    @media (max-width: 900px) {
        .admin-shell {
            flex-direction: column;
        }
        .sidebar {
            position: relative;
            width: 100%;
            height: auto;
            min-height: auto;
            border-right: 0;
            border-bottom: 1px solid var(--line);
        }
        .sidebar-kiln-card {
            display: none;
        }
        .metric-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
        .overview-lower, .overview-bottom {
            grid-template-columns: 1fr !important;
        }
        .setup-steps {
            grid-template-columns: 1fr 1fr !important;
            gap: 12px !important;
        }
        .setup-step + .setup-step {
            border-left: 0 !important;
            padding-left: 0 !important;
        }
    }

    @media (max-width: 600px) {
        .metric-grid {
            grid-template-columns: 1fr !important;
        }
        .setup-steps {
            grid-template-columns: 1fr !important;
        }
        .quick-action-list {
            grid-template-columns: 1fr !important;
        }
    }
</style>
