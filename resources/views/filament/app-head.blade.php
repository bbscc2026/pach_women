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
<style>
    @media (max-width: 1023px) {
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
        font: 500 10px/1 inherit; letter-spacing: .04em; text-transform: uppercase; color: rgb(113 113 122); text-decoration: none;
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
