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
 * Live search for admin index pages (products, orders, users, messages).
 * Attach via x-data="adminLiveSearch('some-results-id')" on a wrapper that
 * contains a <form x-ref="form"> and a #some-results-id results container.
 * Text inputs search after 2+ characters (or 0, to clear); selects/checkboxes
 * search immediately on change. Pagination links inside the results
 * container are intercepted too, so browsing stays a single fetch swap.
 */
Alpine.data('adminLiveSearch', (resultsId) => ({
    loading: false,
    _timer: null,
    _controller: null,

    init() {
        const form = this.$refs.form;
        if (! form) {
            return;
        }

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            this.run(this.buildUrl(form));
        });

        form.querySelectorAll('input[type="search"], input[type="text"]').forEach((el) => {
            el.addEventListener('input', () => {
                const length = el.value.trim().length;
                if (length === 0 || length >= 2) {
                    this.debounced(form);
                }
            });
        });

        form.querySelectorAll('select, input[type="checkbox"]').forEach((el) => {
            el.addEventListener('change', () => this.run(this.buildUrl(form)));
        });

        const results = document.getElementById(resultsId);
        if (results) {
            results.addEventListener('click', (event) => {
                const link = event.target.closest('a[href]');
                if (! link || ! results.contains(link) || ! link.closest('.pagination-wrap')) {
                    return;
                }
                event.preventDefault();
                this.run(link.href);
            });
        }
    },

    buildUrl(form) {
        const params = new URLSearchParams(new FormData(form)).toString();

        return form.action + (params ? '?' + params : '');
    },

    debounced(form) {
        clearTimeout(this._timer);
        this._timer = setTimeout(() => this.run(this.buildUrl(form)), 350);
    },

    async run(url) {
        this._controller?.abort();
        this._controller = new AbortController();
        this.loading = true;

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: this._controller.signal,
            });
            const html = await response.text();
            const results = document.getElementById(resultsId);
            if (results) {
                results.innerHTML = html;
            }
            window.history.replaceState({}, '', url);
        } catch (error) {
            if (error.name !== 'AbortError') {
                throw error;
            }
        } finally {
            this.loading = false;
        }
    },
}));

Alpine.start();
