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

/**
 * Expandable order-detail rows on the admin orders index (see
 * admin/orders/partials/results.blade.php). Each order's <tbody> gets its own
 * scope; the detail fragment (items, customer info, quick actions) is fetched
 * once on first expand and cached, so re-toggling never re-hits the server.
 * Works after an adminLiveSearch swap too, since Alpine auto-initializes
 * x-data on any element added to the DOM.
 */
Alpine.data('orderRow', (detailsUrl) => ({
    open: false,
    loading: false,
    loaded: false,

    async toggle() {
        this.open = ! this.open;
        if (this.open && ! this.loaded) {
            await this.load();
        }
    },

    async load() {
        this.loading = true;
        try {
            const response = await fetch(detailsUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            this.$refs.detail.innerHTML = await response.text();
            this.loaded = true;
        } catch (error) {
            this.$refs.detail.innerHTML = '<p class="px-6 py-8 text-sm text-red-600">Could not load order details. Please try again.</p>';
            this.loaded = true;
        } finally {
            this.loading = false;
        }
    },
}));

/**
 * Admin header notification bell. Attach via
 * x-data="adminNotifications(indexUrl, seenUrl)" (both routes passed in from
 * Blade, mirroring adminLiveSearch above). Polls for new orders/messages so
 * the badge and dropdown update without a page refresh, on every admin page
 * since this lives in layouts/admin.blade.php.
 */
Alpine.data('adminNotifications', (indexUrl, seenUrl) => ({
    open: false,
    loading: false,
    items: [],
    unreadCount: 0,
    _timer: null,

    init() {
        this.refresh();
        this._timer = setInterval(() => this.refresh(), 20000);
        this.$watch('open', (open) => {
            if (open) {
                this.markSeen();
            }
        });
    },

    async refresh() {
        this.loading = true;
        try {
            const { data } = await window.axios.get(indexUrl);
            this.items = data.items;
            this.unreadCount = data.unread_count;
        } catch (error) {
            // Silent — the next poll retries.
        } finally {
            this.loading = false;
        }
    },

    async markSeen() {
        if (this.unreadCount === 0) {
            return;
        }

        this.unreadCount = 0;
        this.items = this.items.map((item) => ({ ...item, unread: false }));

        try {
            await window.axios.post(seenUrl);
        } catch (error) {
            // Next poll re-syncs the true state if this failed.
        }
    },
}));

/**
 * Drag-and-drop reordering for the admin product-edit image galleries (see
 * admin/products/partials/image-gallery-grid.blade.php). Each color's photos
 * sit in their own <div data-sortable-images data-reorder-url data-color>
 * grid; each tile is draggable="true" with data-image-id. Dragging only
 * reorders within the grid it started in — dragover/drop bail out unless the
 * pointer is still over that same source grid, so a photo can never hop into
 * another color's section this way. On drop, the tile's new position in the
 * DOM is read back out and posted as the new order for that color.
 */
let draggingImageId = null;
let draggingSourceGrid = null;

document.addEventListener('dragstart', (event) => {
    const tile = event.target.closest('[data-image-id]');
    const grid = tile ? tile.closest('[data-sortable-images]') : null;
    if (! tile || ! grid) {
        return;
    }

    draggingImageId = tile.dataset.imageId;
    draggingSourceGrid = grid;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', draggingImageId);
    tile.classList.add('opacity-50');
});

document.addEventListener('dragend', (event) => {
    const tile = event.target.closest('[data-image-id]');
    if (tile) {
        tile.classList.remove('opacity-50');
    }
    draggingImageId = null;
    draggingSourceGrid = null;
});

document.addEventListener('dragover', (event) => {
    const grid = event.target.closest('[data-sortable-images]');
    if (! grid || grid !== draggingSourceGrid) {
        return;
    }

    event.preventDefault();

    const dragging = grid.querySelector(`[data-image-id="${draggingImageId}"]`);
    const target = event.target.closest('[data-image-id]');
    if (! dragging || ! target || target === dragging || ! grid.contains(target)) {
        return;
    }

    const rect = target.getBoundingClientRect();
    const after = (event.clientX - rect.left) > rect.width / 2;
    target.parentElement.insertBefore(dragging, after ? target.nextSibling : target);
});

document.addEventListener('drop', (event) => {
    const grid = event.target.closest('[data-sortable-images]');
    if (! grid || grid !== draggingSourceGrid) {
        return;
    }

    event.preventDefault();

    const ids = Array.from(grid.querySelectorAll('[data-image-id]')).map((el) => el.dataset.imageId);

    axios.post(grid.dataset.reorderUrl, { color: grid.dataset.color || null, ids })
        .catch(() => { alert('Failed to save the new photo order. Please try again.'); });
});

Alpine.start();
