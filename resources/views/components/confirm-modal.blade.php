{{--
    Global confirm modal. Any <form data-confirm="Message?"> anywhere on the
    page will show this styled modal instead of the native browser confirm()
    before submitting. See resources/js/app.js for the wiring.
--}}
<div
    x-data="{
        open: false,
        message: '',
        confirmLabel: 'Confirm',
        onConfirm: null,
        init() {
            window.addEventListener('open-confirm-modal', (event) => {
                this.message = event.detail.message;
                this.confirmLabel = event.detail.confirmLabel || 'Confirm';
                this.onConfirm = event.detail.onConfirm;
                this.open = true;
            });
        },
        confirm() {
            this.open = false;
            if (this.onConfirm) {
                this.onConfirm();
            }
        },
    }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[100] flex items-center justify-center p-4"
    @keydown.escape.window="open = false"
    role="alertdialog"
    aria-modal="true"
>
    <div class="fixed inset-0 bg-ink-950/50 backdrop-blur-sm" x-show="open" x-transition.opacity @click="open = false"></div>

    <div
        class="relative w-full max-w-sm rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-elevated"
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
    >
        <p class="text-sm font-medium leading-relaxed text-ink-900" x-text="message"></p>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" class="btn-secondary px-4 py-2 text-sm" @click="open = false">Cancel</button>
            <button type="button" class="btn-danger px-4 py-2 text-sm" @click="confirm()" x-text="confirmLabel"></button>
        </div>
    </div>
</div>
