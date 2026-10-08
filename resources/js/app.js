// Alpine.js ships with Livewire, so no separate import is needed here.

// ---------- App-style back button ----------
// Goes to the previous screen when the visitor navigated inside the shop; otherwise (opened
// straight from a link or the home-screen icon) it goes Home instead of leaving the app.
let navigations = 0;
document.addEventListener('livewire:navigated', () => navigations++);
window.pachBack = () => (navigations > 1 ? history.back() : window.Livewire.navigate('/'));

// ---------- Pull down to refresh (installed app only) ----------
// Installed web apps have no browser refresh, so pulling down at the top of a page reloads it.
(() => {
    const standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
    if (!standalone || !('ontouchstart' in window)) return;

    const THRESHOLD = 80;
    let startY = null;
    let pull = 0;

    const indicator = document.createElement('div');
    indicator.className = 'ptr-indicator';
    indicator.innerHTML = '<span class="ptr-spinner"></span>';
    document.addEventListener('DOMContentLoaded', () => document.body.appendChild(indicator));
    document.addEventListener('livewire:navigated', () => document.body.contains(indicator) || document.body.appendChild(indicator));

    const blocked = () => document.documentElement.classList.contains('overflow-hidden');

    window.addEventListener('touchstart', (e) => {
        startY = window.scrollY <= 0 && !blocked() ? e.touches[0].clientY : null;
        pull = 0;
    }, { passive: true });

    window.addEventListener('touchmove', (e) => {
        if (startY === null) return;
        pull = Math.max(0, Math.min(140, (e.touches[0].clientY - startY) * 0.5));
        indicator.style.transform = `translate(-50%, ${pull - 48}px) rotate(${pull * 3}deg)`;
        indicator.classList.toggle('is-ready', pull >= THRESHOLD);
    }, { passive: true });

    window.addEventListener('touchend', () => {
        if (startY === null) return;
        if (pull >= THRESHOLD) {
            indicator.classList.add('is-loading');
            window.location.reload();
        } else {
            indicator.style.transform = '';
        }
        startY = null;
    });
})();

// Opens Razorpay Checkout when the Checkout component asks for it,
// then posts the payment result to the server for signature verification.
document.addEventListener('livewire:init', () => {
    Livewire.on('razorpay-open', ([options]) => {
        if (typeof window.Razorpay === 'undefined') {
            alert('Payment window could not load. Please check your connection and try again.');
            return;
        }

        const submit = (fields) => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = options.verify_url;

            Object.entries({ _token: options.csrf, order: options.order_number, ...fields }).forEach(([name, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        };

        const rzp = new window.Razorpay({
            key: options.key,
            amount: options.amount,
            currency: 'INR',
            name: options.name,
            description: `Order ${options.order_number}`,
            order_id: options.razorpay_order_id,
            prefill: options.prefill,
            theme: { color: '#141414' },
            handler: (response) => submit(response),
            modal: {
                ondismiss: () => window.location.assign(options.cancel_url),
            },
        });

        rzp.open();
    });
});
