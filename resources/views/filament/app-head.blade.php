{{-- Admin <head> additions: installable app, offline support, and room for the phone tab bar. --}}
<link rel="manifest" href="/admin.webmanifest">
<meta name="theme-color" content="#141414">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="apple-mobile-web-app-title" content="PACH Admin">
<meta name="pwa-install-banner" content="auto">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
<script src="/pwa.js" defer data-navigate-once></script>
<script src="/admin-mobile.js" defer data-navigate-once></script>
<style>
    /* Compact workspace: reduce empty space without shrinking touch controls. */
    .pw-select-toggle { display: none; }
    .fi-main { max-width: none !important; padding-inline: 20px !important; }
    .fi-page { padding-block: 20px !important; row-gap: 16px !important; }
    .fi-page-header-main-ctn, .fi-page-content { gap: 16px !important; }
    .fi-header { gap: 12px !important; }
    .fi-header-heading { font-size: 24px !important; line-height: 1.25 !important; }
    .fi-section-header { padding: 14px 16px !important; }
    .fi-section-content { padding: 16px !important; }
    .fi-ta-header, .fi-ta-header-toolbar { padding: 12px 16px !important; }
    .fi-ta-record { padding-block: 12px !important; }
    .fi-ta-record-content { gap: 12px !important; }
    .fi-ta-record-actions { gap: 8px !important; }
    .fi-wi-stats-overview-stat { padding: 16px !important; }
    .fi-sidebar-nav { padding: 12px !important; gap: 16px !important; }
    .fi-sidebar-item-button { padding-block: 9px !important; }
    .fi-section, .fi-ta-ctn { border-radius: 12px !important; }
    @media (max-width: 1023px) {
        .pw-select-toggle { display: inline-flex; align-items: center; }
        .fi-ta-record { position: relative; padding: 10px 12px !important; }
        .fi-ta-record-content-ctn, .fi-ta-record-content, .fi-ta-col, .fi-ta-text { min-width: 0; }
        .fi-ta-text { overflow-wrap: anywhere; white-space: normal !important; }
        .fi-ta-record-with-content-prefix { padding-inline-start: 12px !important; }
        .fi-ta-record-checkbox, .fi-ta-page-checkbox { position: absolute !important; width: 1px !important; height: 1px !important; opacity: 0; }
        .fi-ta-record:has(.fi-ta-record-checkbox:checked) { background: rgb(59 130 246 / .12); box-shadow: inset 3px 0 #3b82f6; }
        .fi-ta-record-content { padding: 0 !important; }
        .fi-ta-record-actions { padding-top: 6px !important; }
        .pw-select-toggle { margin: 8px 12px; min-height: 44px; padding: 8px 12px; border: 1px solid #71717a; border-radius: 8px; font: inherit; }
        .fi-main { padding-inline: 12px !important; }
        .fi-page { padding-block: 14px !important; }
        .fi-header-heading { font-size: 22px !important; }
        .fi-section-content { padding: 12px !important; }
        .fi-ta-header, .fi-ta-header-toolbar { padding: 10px 12px !important; }
        .fi-input, .fi-input-wrp input, .fi-input-wrp textarea, .fi-input-wrp select { font-size: 16px !important; }
        .fi-ta-record-actions button, .fi-ta-record-actions a { min-height: 44px; }
        /* Leave space for the tab bar so the last rows and save buttons aren't hidden. */
        body.fi-body { padding-bottom: calc(68px + env(safe-area-inset-bottom)); }
        .fi-sc-actions.fi-sticky, .fi-form-actions.fi-sticky { bottom: calc(68px + env(safe-area-inset-bottom)) !important; }
    }
    .pw-tabbar { position: fixed; inset: auto 0 0 0; z-index: 40; display: flex; height: calc(64px + env(safe-area-inset-bottom));
        padding-bottom: env(safe-area-inset-bottom); background: rgba(255,255,255,.96); border-top: 1px solid rgb(0 0 0 / .08);
        backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); }
    .dark .pw-tabbar { background: rgba(24,24,27,.96); border-top-color: rgb(255 255 255 / .08); }
    @media (min-width: 1024px) { .pw-tabbar { display: none; } }
    .pw-tab { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px;
        font-family: inherit; font-weight: 500; font-size: 10px; line-height: 1; letter-spacing: .02em; color: rgb(113 113 122); text-decoration: none;
        background: none; border: 0; cursor: pointer; position: relative; -webkit-tap-highlight-color: transparent; }
    .pw-tab svg { width: 24px; height: 24px; }
    .pw-tab.is-active { color: rgb(24 24 27); }
    .dark .pw-tab.is-active { color: #fff; }
    .pw-tab-badge { position: absolute; top: 8px; left: calc(50% + 6px); min-width: 18px; height: 18px; padding: 0 5px; border-radius: 9999px;
        background: #b4372f; color: #fff; font: 600 10px/18px inherit; text-align: center; }
    .pw-tab-add span.pw-plus { display: flex; align-items: center; justify-content: center; width: 46px; height: 46px; margin-top: -18px;
        border-radius: 9999px; background: #141414; color: #fff; box-shadow: 0 6px 16px rgb(0 0 0 / .25); }
    .dark .pw-tab-add span.pw-plus { background: #fff; color: #141414; }
    .pw-tab-add { color: inherit; }
</style>
