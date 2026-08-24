import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

/**
 * Global cart badge state. Bootstrapped from the server-rendered count on
 * every full page load (see components/cart-fab.blade.php), then kept in
 * sync client-side by the data-cart-form handler below without reloading.
 */
Alpine.store('cart', {
    count: 0,
    subtotal: '0.00',
});

/**
 * Any <form data-cart-form> (add to cart, update quantity, remove line)
 * submits via fetch instead of a full page reload. Cooperates with
 * data-confirm above — if both are present, this waits for the confirm
 * modal's requestSubmit() before intercepting.
 *
 * On success the global cart store updates instantly (badge everywhere),
 * and if the form lives inside #cart-body, that container is swapped with
 * the fresh server-rendered fragment so quantity/removal edits show
 * immediately. A `cart-updated` (or `cart-error`) window event is also
 * dispatched for any page-specific feedback (see shop/product.blade.php).
 */
document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (! (form instanceof HTMLFormElement) || ! form.hasAttribute('data-cart-form')) {
        return;
    }

    if (form.hasAttribute('data-confirm') && form.dataset.confirmed !== 'true') {
        return;
    }

    event.preventDefault();

    const submitter = event.submitter;
    if (submitter) {
        submitter.disabled = true;
    }

    try {
        const response = await fetch(form.action, {
            method: form.method || 'POST',
            body: new FormData(form),
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const data = await response.json();

        if (! response.ok) {
            /**
             * A rejected quantity update (e.g. asking for more than the
             * current stock) must not leave the input showing the rejected
             * number — that reads as "it worked" when nothing was actually
             * saved. Snap it back to the last value the server confirmed.
             */
            const qtyInput = form.querySelector('[data-qty-live]');
            if (qtyInput) {
                qtyInput.value = qtyInput.defaultValue;
            }

            window.dispatchEvent(new CustomEvent('cart-error', {
                detail: { message: data.message ?? 'Something went wrong.' },
            }));

            return;
        }

        if (typeof data.count === 'number') {
            Alpine.store('cart').count = data.count;
        }
        if (typeof data.subtotal === 'string') {
            Alpine.store('cart').subtotal = data.subtotal;
        }

        const cartBody = document.getElementById('cart-body');
        if (cartBody && typeof data.html === 'string') {
            cartBody.innerHTML = data.html;
        }

        window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
    } catch (error) {
        window.dispatchEvent(new CustomEvent('cart-error', {
            detail: { message: 'Network error. Please try again.' },
        }));
    } finally {
        if (submitter) {
            submitter.disabled = false;
        }
    }
});

/**
 * Cart quantity inputs (data-qty-live, see shop/partials/cart-body.blade.php)
 * auto-submit their form so the line/subtotal price stays live instead of
 * waiting for the whole form to be submitted by hand. Two triggers, both
 * idempotent so it's safe for both to fire for the same edit:
 *  - "change" is the reliable, browser-native trigger (blur after typing,
 *    or clicking the number input's spinner arrows) and always fires.
 *  - the debounced "input" listener additionally catches the case where the
 *    field never blurs (e.g. the user reads the result without tabbing
 *    away), so the total still updates a moment after typing stops.
 */
function submitQtyForm(input) {
    if (! (input instanceof HTMLInputElement) || ! input.hasAttribute('data-qty-live')) {
        return;
    }

    if (input.value !== '' && input.reportValidity()) {
        input.form?.requestSubmit();
    }
}

const qtyLiveTimers = new WeakMap();

document.addEventListener('input', (event) => {
    const input = event.target;
    if (! (input instanceof HTMLInputElement) || ! input.hasAttribute('data-qty-live')) {
        return;
    }

    clearTimeout(qtyLiveTimers.get(input));
    qtyLiveTimers.set(input, setTimeout(() => submitQtyForm(input), 500));
});

document.addEventListener('change', (event) => {
    const input = event.target;
    if (! (input instanceof HTMLInputElement) || ! input.hasAttribute('data-qty-live')) {
        return;
    }

    clearTimeout(qtyLiveTimers.get(input));
    submitQtyForm(input);
});

/**
 * Any <form data-confirm="Message?"> submits through the styled confirm
 * modal (see resources/views/components/confirm-modal.blade.php) instead of
 * the native browser confirm(). Optional data-confirm-label sets the button
 * text (defaults to "Confirm").
 */
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (! (form instanceof HTMLFormElement) || ! form.hasAttribute('data-confirm')) {
        return;
    }

    if (form.dataset.confirmed === 'true') {
        return;
    }

    event.preventDefault();

    window.dispatchEvent(new CustomEvent('open-confirm-modal', {
        detail: {
            message: form.getAttribute('data-confirm'),
            confirmLabel: form.getAttribute('data-confirm-label'),
            onConfirm: () => {
                form.dataset.confirmed = 'true';
                form.requestSubmit();
            },
        },
    }));
});

/**
 * Admin pages (layouts/admin.blade.php sets <body data-admin-bundle>) need a
 * meaningfully larger chunk of Alpine components — live search, notification
 * polling, drag-and-drop, the media library picker — that storefront pages
 * never touch. Loading admin.js as a dynamic import keeps that code out of
 * the bundle every shop/cart/checkout visitor downloads; it must resolve
 * (and register its Alpine.data/store calls) before Alpine.start() runs, or
 * any x-data="adminLiveSearch(...)" etc. already in the DOM would fail to
 * find their component.
 */
(async () => {
    if (document.body.dataset.adminBundle) {
        await import('./admin');
    }
    Alpine.start();
})();
