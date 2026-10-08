/*
 * PACH WOMEN app helper, shared by the shop and the admin (plain JS, no build step).
 *  - registers the service worker (/sw.js) for offline use
 *  - shows "New version available — Update" after a deploy
 *  - shows a slim "You're offline" bar when the connection drops
 *  - powers "Install app" buttons ([data-pwa-install]) and the iPhone hint ([data-pwa-ios-hint])
 */
(() => {
    if (window.__pachPwa) return;
    window.__pachPwa = true;

    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
    const autoInstallBanner = document.querySelector('meta[name="pwa-install-banner"]')?.content === 'auto';

    // ---------- Styles (scoped by the pw- prefix, work on both shop and admin) ----------
    const style = document.createElement('style');
    style.textContent = `
        .pw-toast{position:fixed;left:50%;transform:translateX(-50%);z-index:2147483000;display:flex;align-items:center;gap:12px;
            max-width:calc(100vw - 24px);padding:10px 10px 10px 16px;border-radius:9999px;background:#141414;color:#fff;
            font:500 14px/1.3 system-ui,-apple-system,"Segoe UI",sans-serif;box-shadow:0 10px 30px rgba(0,0,0,.25);
            bottom:calc(env(safe-area-inset-bottom) + 84px);animation:pw-in .25s ease-out}
        @media (min-width:1024px){.pw-toast{bottom:24px}}
        .pw-toast>span{white-space:nowrap}
        .pw-toast button{border:0;border-radius:9999px;padding:8px 14px;font:600 13px/1 inherit;cursor:pointer}
        .pw-primary{background:#fff;color:#141414}
        .pw-ghost{background:transparent;color:#fff;opacity:.7;padding:8px!important}
        .pw-offline{position:fixed;top:0;left:0;right:0;z-index:2147483001;padding:calc(env(safe-area-inset-top) + 6px) 12px 6px;
            background:#b4372f;color:#fff;text-align:center;font:500 12px/1.3 system-ui,-apple-system,"Segoe UI",sans-serif;
            letter-spacing:.02em;animation:pw-down .25s ease-out}
        [data-pwa-install],[data-pwa-ios-hint]{display:none!important}
        .pw-can-install [data-pwa-install]{display:inline-flex!important}
        .pw-ios [data-pwa-ios-hint]{display:block!important}
        @keyframes pw-in{from{opacity:0;transform:translate(-50%,10px)}to{opacity:1;transform:translate(-50%,0)}}
        @keyframes pw-down{from{transform:translateY(-100%)}to{transform:none}}
        @media (prefers-reduced-motion:reduce){.pw-toast,.pw-offline{animation:none}}
    `;
    document.head.appendChild(style);

    const toast = (html, onAction, actionLabel, onClose) => {
        document.querySelector('.pw-toast')?.remove();
        const el = document.createElement('div');
        el.className = 'pw-toast';
        el.setAttribute('role', 'status');
        el.innerHTML = `<span>${html}</span>`;
        const action = document.createElement('button');
        action.className = 'pw-primary';
        action.textContent = actionLabel;
        action.onclick = () => { el.remove(); onAction(); };
        const close = document.createElement('button');
        close.className = 'pw-ghost';
        close.setAttribute('aria-label', 'Dismiss');
        close.textContent = '✕';
        close.onclick = () => { el.remove(); onClose?.(); };
        el.append(action, close);
        document.body.appendChild(el);
    };

    // ---------- Offline bar ----------
    const updateOnline = () => {
        document.querySelector('.pw-offline')?.remove();
        if (!navigator.onLine) {
            const bar = document.createElement('div');
            bar.className = 'pw-offline';
            bar.setAttribute('role', 'status');
            bar.textContent = "You're offline — showing saved pages. Changes need internet.";
            document.body.appendChild(bar);
        }
    };
    window.addEventListener('online', updateOnline);
    window.addEventListener('offline', updateOnline);
    document.addEventListener('livewire:navigated', updateOnline);
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', updateOnline); else updateOnline();

    // ---------- Service worker + updates ----------
    if ('serviceWorker' in navigator) {
        let reloading = false;
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            if (reloading) return;
            reloading = true;
            window.location.reload();
        });

        const offerUpdate = (worker) => toast('New version available', () => worker.postMessage('SKIP_WAITING'), 'Update');

        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js', { scope: '/' }).then((registration) => {
                if (registration.waiting && navigator.serviceWorker.controller) offerUpdate(registration.waiting);

                registration.addEventListener('updatefound', () => {
                    const worker = registration.installing;
                    worker?.addEventListener('statechange', () => {
                        if (worker.state === 'installed' && navigator.serviceWorker.controller) offerUpdate(worker);
                    });
                });

                // Check for a new version when the app comes back to the foreground, and every 30 minutes.
                document.addEventListener('visibilitychange', () => document.visibilityState === 'visible' && registration.update());
                setInterval(() => registration.update(), 30 * 60 * 1000);
            }).catch(() => {});
        });
    }

    // ---------- Install ----------
    let deferredPrompt = null;
    const root = document.documentElement;

    window.pachInstall = async () => {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        await deferredPrompt.userChoice;
        deferredPrompt = null;
        root.classList.remove('pw-can-install');
    };

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;
        root.classList.add('pw-can-install');

        let dismissed = false;
        try { dismissed = localStorage.getItem('pw-install-dismissed') === '1'; } catch (e) {}
        if (autoInstallBanner && !dismissed) {
            toast('Install the admin app on this phone', window.pachInstall, 'Install', () => {
                try { localStorage.setItem('pw-install-dismissed', '1'); } catch (e) {}
            });
        }
    });

    window.addEventListener('appinstalled', () => root.classList.remove('pw-can-install'));

    if (isIos && !isStandalone) root.classList.add('pw-ios');

    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-pwa-install]')) {
            event.preventDefault();
            window.pachInstall();
        }
    });
})();
