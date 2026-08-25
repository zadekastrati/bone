/**
 * Admin-only Alpine components. Dynamically imported from app.js (only when
 * <body data-admin-bundle> is present, see layouts/admin.blade.php) so
 * storefront visitors never download this bundle — it's a meaningful chunk
 * of JS (live search, notifications polling, drag-and-drop, the media
 * library picker) that has no reason to ship to shop/cart/checkout pages.
 *
 * Uses window.Alpine (already set by app.js before this import runs) rather
 * than importing 'alpinejs' again, so there's only ever one Alpine instance.
 */
const Alpine = window.Alpine;

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

/**
 * Shared state for the admin "choose from library" picker (see
 * admin/products/partials/image-gallery.blade.php and the
 * admin.product-media-library-picker / admin.media-library-modal components).
 * One fetch of the R2 file list is shared across every color section's picker
 * on the page; pickedPaths tracks files chosen but not yet saved, so the same
 * file can't be picked into two color sections in one editing session.
 */
Alpine.store('mediaLibrary', {
    loading: false,
    error: null,
    files: [],
    pickedPaths: [],
    // Re-fetches every time the picker opens rather than caching after the first
    // load — files get added to R2 directly (outside the app) at any time, so a
    // stale in-memory list would hide anything uploaded after the picker was
    // first opened on the page, even after closing and reopening it.
    async ensureLoaded(url) {
        if (this.loading) {
            return;
        }
        this.loading = true;
        this.error = null;
        try {
            const response = await axios.get(url);
            // `visible` and `selected` are per-tile UI flags, not server data — mutated
            // directly on these same objects by the modal below so that touching one
            // tile only re-renders that tile, not the whole ~250-file grid.
            this.files = (response.data.files || []).map((file) => ({ ...file, visible: false, selected: false }));
        } catch (error) {
            this.error = 'Could not load the media library. Please try again.';
        } finally {
            this.loading = false;
        }
    },
    available() {
        return this.files.filter((file) => ! this.pickedPaths.includes(file.path));
    },
    markPicked(paths) {
        paths.forEach((path) => {
            if (! this.pickedPaths.includes(path)) {
                this.pickedPaths.push(path);
            }
        });
    },
    unmark(path) {
        this.pickedPaths = this.pickedPaths.filter((p) => p !== path);
    },
});

Alpine.data('mediaLibraryModal', (fetchUrl, thumbnailUrl) => ({
    open: false,
    view: 'folders', // 'folders' | 'files' — browsing by folder first keeps each
    // rendered grid down to ~100 tiles instead of all ~276 at once, which is what was
    // actually making the picker feel sluggish (see folders getter + openFolder below).
    activeFolder: null,
    query: '',
    onSelect: null,
    observer: null,
    /**
     * These R2 photos are full camera originals (often 25-30+ megapixels) — a
     * browser has to decode the whole thing before it can even downscale it
     * for a ~150px grid tile, and that decode cost, not network speed, is what
     * was actually freezing the tab when several loaded together. Images route
     * through the server-side resize+cache endpoint instead of the raw R2
     * file; videos still use the original (no equivalent problem there).
     */
    thumbSrc(item) {
        return thumbnailUrl + '?path=' + encodeURIComponent(item.path);
    },
    async openFor(detail) {
        this.onSelect = detail?.onSelect ?? null;
        this.view = 'folders';
        this.activeFolder = null;
        this.query = '';
        this.resetRevealThrottle();
        this.open = true;
        await Alpine.store('mediaLibrary').ensureLoaded(fetchUrl);
        Alpine.store('mediaLibrary').files.forEach((file) => { file.selected = false; });
    },
    close() {
        this.open = false;
    },
    toggle(item) {
        item.selected = ! item.selected;
    },
    /**
     * One card per R2 folder, with counts — computed from the same already-fetched
     * file list, no extra request. Selecting a folder is what actually bounds the
     * grid to a manageable size; this view itself never renders any thumbnails.
     */
    get folders() {
        const byFolder = {};
        Alpine.store('mediaLibrary').available().forEach((file) => {
            const key = file.folder ?? 'Other files';
            if (! byFolder[key]) {
                byFolder[key] = { name: key, total: 0, videos: 0, images: 0 };
            }
            byFolder[key].total++;
            if (file.is_video) {
                byFolder[key].videos++;
            } else {
                byFolder[key].images++;
            }
        });
        return Object.values(byFolder).sort((a, b) => a.name.localeCompare(b.name));
    },
    openFolder(name) {
        this.activeFolder = name;
        this.view = 'files';
        this.query = '';
        this.resetRevealThrottle();
    },
    backToFolders() {
        this.view = 'folders';
        this.activeFolder = null;
        this.query = '';
        this.resetRevealThrottle();
    },
    /**
     * Leaving a folder mid-load aborts whatever tiles were still fetching (their
     * <li> gets removed from the DOM by x-for), so their @load/@error never fires
     * and activeReveals would stay stuck too high — permanently over-throttling
     * the next folder. Clearing the queue/counter on every folder change avoids that.
     */
    resetRevealThrottle() {
        this.revealQueue = [];
        this.activeReveals = 0;
        this.activeVideoReveals = 0;
    },
    get filtered() {
        const q = this.query.trim().toLowerCase();
        const items = Alpine.store('mediaLibrary').available()
            .filter((item) => (item.folder ?? 'Other files') === this.activeFolder);
        if (! q) {
            return items;
        }
        return items.filter((item) => item.path.toLowerCase().includes(q));
    },
    get selectedCount() {
        return Alpine.store('mediaLibrary').files.filter((file) => file.selected).length;
    },
    confirm() {
        const items = Alpine.store('mediaLibrary').files.filter((file) => file.selected);
        if (items.length && this.onSelect) {
            this.onSelect(items);
            Alpine.store('mediaLibrary').markPicked(items.map((file) => file.path));
        }
        this.close();
    },
    /**
     * The picker previously revealed every tile within the scroll margin the
     * moment it intersected, so opening a 100-file folder — or making one
     * scroll gesture that crossed 20+ tiles at once — fired that many
     * multi-megabyte R2 fetches AND full-resolution image decodes
     * simultaneously. Browsers decode an <img> at its real pixel dimensions
     * before ever considering the tiny thumbnail size it's displayed at, so a
     * burst of these (some of these photos are 10MB+ originals) is what was
     * actually freezing the tab, not just the network. Tiles now queue
     * instead of revealing immediately, and only a handful load at a time —
     * see queueReveal/pumpQueue/tileSettled below.
     */
    observeTile(el, item) {
        if (item.visible || item.queued) {
            return;
        }
        if (! this.observer) {
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (! entry.isIntersecting) {
                        return;
                    }
                    const match = Alpine.store('mediaLibrary').files.find((file) => file.path === entry.target.dataset.path);
                    this.observer.unobserve(entry.target);
                    if (match) {
                        this.queueReveal(match);
                    }
                });
            }, { root: this.$refs.scrollContainer, rootMargin: '150px 0px', threshold: 0.01 });
        }
        this.observer.observe(el);
    },
    revealQueue: [],
    activeReveals: 0,
    activeVideoReveals: 0,
    // Higher now that thumbnails are cached durably on R2 (see ImageVariantCache) —
    // most reveals are a fast cache hit, not a cold R2-fetch-and-resize, so there's
    // little decode-storm risk left to throttle against. Only applies to images.
    maxConcurrentReveals: 24,
    // Videos are a completely different cost: each one spins up a real browser
    // video decoder, a much scarcer resource than a cached JPEG request. Revealing
    // too many at once (the same limit as images) made most of them stall forever
    // without ever firing loadedmetadata — permanent black tiles, not just slow
    // ones, since a stuck reveal never frees its slot for the next item either.
    maxConcurrentVideoReveals: 4,
    queueReveal(item) {
        item.queued = true;
        this.revealQueue.push(item);
        this.pumpRevealQueue();
    },
    pumpRevealQueue() {
        // Can't just take the head of the queue and stop at the first blocked
        // item — images and videos are throttled independently, so a blocked
        // video at the front shouldn't also block an eligible image behind it.
        for (let i = 0; i < this.revealQueue.length; i++) {
            const item = this.revealQueue[i];
            const limit = item.is_video ? this.maxConcurrentVideoReveals : this.maxConcurrentReveals;
            const active = item.is_video ? this.activeVideoReveals : this.activeReveals;
            if (active >= limit) {
                continue;
            }
            this.revealQueue.splice(i, 1);
            i--;
            if (item.is_video) {
                this.activeVideoReveals++;
            } else {
                this.activeReveals++;
            }
            item.visible = true;
        }
    },
    /** Fires on the tile's real <img>/<video> load or error — either way, frees its slot. */
    tileSettled(item) {
        if (item.settled) {
            return;
        }
        item.settled = true;
        if (item.is_video) {
            this.activeVideoReveals = Math.max(0, this.activeVideoReveals - 1);
        } else {
            this.activeReveals = Math.max(0, this.activeReveals - 1);
        }
        this.pumpRevealQueue();
    },
}));
