// Alpine.js ships with Livewire, so no separate import is needed here.

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
