import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

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
