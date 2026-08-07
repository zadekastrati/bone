@props([
    'inputId' => 'product-media-upload',
    'label' => 'Upload images or video',
])

<div
    x-data="{
        files: [],
        previews: [],
        fileKey(file) {
            return `${file.name}-${file.size}-${file.lastModified}`;
        },
        addFiles(event) {
            const incoming = Array.from(event.target.files || []);
            incoming.forEach((file) => {
                const key = this.fileKey(file);
                if (! this.files.some((existing) => this.fileKey(existing) === key)) {
                    this.files.push(file);
                }
            });
            this.syncInput();
            this.refreshPreviews();
            event.target.value = '';
        },
        removeFile(index) {
            if (this.previews[index]?.url) {
                URL.revokeObjectURL(this.previews[index].url);
            }
            this.files.splice(index, 1);
            this.syncInput();
            this.refreshPreviews();
        },
        syncInput() {
            const dt = new DataTransfer();
            this.files.forEach((file) => {
                try {
                    dt.items.add(file);
                } catch (error) {
                    console.error('Could not attach file to upload input.', file.name, error);
                }
            });
            this.$refs.fileInput.files = dt.files;
        },
        refreshPreviews() {
            this.previews.forEach((preview) => {
                if (preview.url) {
                    URL.revokeObjectURL(preview.url);
                }
            });
            this.previews = this.files.map((file) => ({
                url: URL.createObjectURL(file),
                isVideo: file.type.startsWith('video/') || /\.(mp4|webm|mov|ogg|m4v)$/i.test(file.name),
                name: file.name,
            }));
        },
        init() {
            const form = this.$root.closest('form');
            if (form) {
                form.addEventListener('submit', () => this.syncInput());
            }
        },
    }"
    {{ $attributes->merge(['class' => 'space-y-3']) }}
>
    <label for="{{ $inputId }}" class="form-label">{{ $label }}</label>
    <input
        x-ref="fileInput"
        id="{{ $inputId }}"
        type="file"
        name="images[]"
        accept="image/*,video/*,.jpg,.jpeg,.png,.webp,.mp4,.webm,.mov,.ogg,.m4v"
        multiple
        class="form-input"
        @change="addFiles($event)"
    >
    <ul class="space-y-2" x-show="previews.length" x-cloak>
        <template x-for="(preview, index) in previews" :key="preview.name + '-' + index">
            <li class="flex items-center gap-3 rounded-xl border border-ink-200/60 bg-white/80 px-3 py-2">
                <span class="relative inline-block size-14 shrink-0 overflow-hidden rounded-lg ring-1 ring-ink-200/60" x-show="preview.isVideo">
                    <video :src="preview.url" class="size-full object-cover" muted playsinline></video>
                    <span class="pointer-events-none absolute inset-0 flex items-center justify-center bg-ink-950/45" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1" fill="#fff"/><rect x="14" y="5" width="4" height="14" rx="1" fill="#fff"/></svg>
                    </span>
                </span>
                <img x-show="!preview.isVideo" :src="preview.url" alt="" class="size-14 rounded-lg object-cover ring-1 ring-ink-200/60">
                <span class="min-w-0 flex-1 truncate text-sm text-ink-700" x-text="preview.name"></span>
                <button
                    type="button"
                    class="inline-flex size-8 shrink-0 items-center justify-center rounded-md text-zinc-500 transition hover:bg-red-50 hover:text-red-700"
                    @click="removeFile(index)"
                    title="Remove file"
                    aria-label="Remove selected file"
                >
                    <x-icons.trash class="h-4 w-4" />
                </button>
            </li>
        </template>
    </ul>
    <p class="text-xs text-ink-500">JPEG, PNG, WebP, or MP4 / WebM / MOV video. You can add files in multiple steps before saving. The first photo uploaded becomes the catalog thumbnail; change it later when editing.</p>
</div>
