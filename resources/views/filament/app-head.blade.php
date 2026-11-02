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
<script src="{{ asset('admin-mobile.js') }}?v={{ filemtime(public_path('admin-mobile.js')) }}" defer data-navigate-once></script>
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
    .pw-products-table .pw-product-row { align-items: center; gap: 20px; }
    .pw-products-table .pw-product-price { width: 180px; }
    .pw-products-table .fi-ta-image img { width: 48px; height: 64px; object-fit: cover; border-radius: 8px; }
    .pw-products-table .fi-ta-record { position: relative; }
    .pw-products-table .fi-ta-record-content-ctn { padding-block: 0 !important; }
    .pw-products-table .fi-input-wrp { border-radius: 8px !important; }
    .pw-categories-table .pw-category-row { align-items: center; gap: 20px; }
    .pw-categories-table .fi-ta-record { position: relative; }
    .pw-categories-table .fi-ta-record-content-ctn { padding-block: 0 !important; }
    .pw-categories-table .fi-ta-image img { width: 48px; height: 64px; object-fit: cover; border-radius: 8px; }
    @media (max-width: 1023px) {
        .fi-resource-categories .fi-header { flex-direction: row !important; align-items: center !important; justify-content: space-between; }
        .pw-categories-table .fi-ta-record { padding: 14px 12px !important; }
        .pw-categories-table .fi-ta-record-content-ctn { display: block; width: 100%; padding: 0 !important; }
        .pw-categories-table .pw-category-row { display: grid !important; grid-template-columns: 64px minmax(0, 1fr); align-items: center !important; gap: 8px 12px !important; }
        .pw-categories-table .pw-category-row > .fi-ta-col:first-child { grid-row: 1 / 3; }
        .pw-categories-table .pw-category-row > :not(:first-child) { grid-column: 2; }
        .pw-categories-table .fi-ta-image img { width: 64px !important; height: 86px !important; }
        .pw-categories-table .pw-category-row > .fi-ta-col:last-child { min-height: 40px; display: flex; align-items: center; padding-right: 90px; }
        .pw-categories-table .fi-ta-record-content-ctn > .fi-ta-actions { position: absolute; right: 12px; bottom: 14px; margin: 0; }
        .pw-categories-table .fi-ta-actions .fi-btn { min-height: 40px; padding-inline: 12px; border-radius: 8px; }
        .fi-resource-products .fi-header { flex-direction: row !important; align-items: center !important; justify-content: space-between; }
        .pw-products-table .fi-ta-header-toolbar { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; }
        .pw-products-table .pw-select-toggle { margin: 0; padding: 8px 10px; font-size: 12px; }
        .pw-products-table .fi-ta-record { padding: 14px 12px !important; }
        .pw-products-table .fi-ta-record-content-ctn { display: block; width: 100%; padding: 0 !important; }
        .pw-products-table .pw-product-row { display: grid !important; grid-template-columns: 64px minmax(0, 1fr); align-items: start !important; gap: 8px 12px !important; }
        .pw-products-table .pw-product-row > .fi-ta-col:first-child { grid-row: 1 / 4; }
        .pw-products-table .pw-product-row > :not(:first-child) { grid-column: 2; }
        .pw-products-table .pw-product-price { width: auto; display: flex !important; flex-direction: row !important; align-items: center; flex-wrap: wrap; gap: 8px !important; }
        .pw-products-table .pw-product-price > * { flex: none !important; }
        .pw-products-table .fi-ta-image img { width: 64px !important; height: 86px !important; }
        .pw-products-table .fi-ta-stack { gap: 4px !important; }
        .pw-products-table .fi-ta-text { line-height: 1.35; }
        .pw-products-table .pw-product-stock { display: block !important; width: 108px !important; max-width: 108px !important; }
        .pw-products-table .pw-product-stock .fi-input-wrp { width: 108px !important; min-width: 0 !important; }
        .pw-products-table .pw-product-stock .fi-input-wrp-content-ctn, .pw-products-table .pw-product-stock input { min-width: 0 !important; width: 100%; }
        .pw-products-table .fi-ta-record-content-ctn > .fi-ta-actions { position: absolute; right: 12px; bottom: 14px; margin: 0; }
        .pw-products-table .fi-ta-actions .fi-btn { min-height: 40px; padding-inline: 12px; border-radius: 8px; }
        .pw-products-table .fi-input-wrp { border-radius: 8px; }
        .fi-wi-stats-overview .fi-section-content-ctn { min-width: 0; }
        .fi-wi-stats-overview-stat { min-width: 0; padding: 14px !important; }
        .fi-wi-stats-overview-stat-label { font-size: 12px !important; overflow-wrap: anywhere; }
        .fi-wi-stats-overview-stat-value { font-size: 25px !important; line-height: 1.2; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
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
    .pw-create-panel { position: fixed; bottom: calc(82px + env(safe-area-inset-bottom)); left: 12px; right: 12px; max-width: 440px; margin-inline: auto; padding: 12px; border: 1px solid #e4e4e7; border-radius: 18px; background: #fff; color: #18181b; box-shadow: 0 12px 45px rgb(0 0 0 / .25); }
    .dark .pw-create-panel { background: #18181b; color: #fafafa; border-color: #3f3f46; }
    .pw-create-heading { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding-left: 8px; font-size: 14px; }
    .pw-create-heading button { min-width: 44px; min-height: 44px; border: 0; background: none; color: inherit; cursor: pointer; }
    .pw-create-panel > a { display: flex; align-items: center; gap: 12px; padding: 14px 10px; border-radius: 10px; text-decoration: none; color: inherit; }
    .pw-create-panel > a:hover, .pw-create-panel > a:focus-visible { background: rgb(113 113 122 / .15); }
    .pw-create-panel > a > svg { width: 24px; height: 24px; flex-shrink: 0; }
    .pw-create-panel > a > span { flex: 1; min-width: 0; }
    .pw-create-panel strong { display: block; font-size: 14px; }
    .pw-create-panel small { display: block; font-size: 12px; opacity: .75; margin-top: 3px; }
</style>
