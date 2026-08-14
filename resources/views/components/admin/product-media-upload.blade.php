@props([
    'inputId' => 'product-media-upload',
    'inputName' => 'images[]',
    'label' => 'Add images or video',
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
    {{ $attributes->merge(['class' => 'space-y-4']) }}
>
    <div class="group/drop relative flex flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-ink-200/90 bg-zinc-50/60 px-6 py-8 text-center transition hover:border-accent-300 hover:bg-accent-50/40">
        <input
            x-ref="fileInput"
            id="{{ $inputId }}"
            type="file"
            name="{{ $inputName }}"
            accept="image/*,video/*,.jpg,.jpeg,.png,.webp,.mp4,.webm,.mov,.ogg,.m4v"
            multiple
            class="absolute inset-0 z-10 cursor-pointer opacity-0"
            @change="addFiles($event)"
        >
        <span class="inline-flex size-11 items-center justify-center rounded-full bg-accent-50 text-accent-600 ring-1 ring-accent-100 transition group-hover/drop:bg-accent-100">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3.75 3.75 0 0 1 4.177 3.815 4.5 4.5 0 0 1-1.41 8.775h-11.59Z" />
            </svg>
        </span>
        <p class="text-sm font-semibold text-ink-800">{{ $label }}</p>
        <p class="text-xs text-ink-500">JPEG, PNG, WebP, or MP4 / WebM / MOV. Click to browse or drop files here.</p>
    </div>

    <ul class="grid grid-cols-6 gap-3" x-show="previews.length" x-cloak>
        <template x-for="(preview, index) in previews" :key="preview.name + '-' + index">
            <li class="group relative aspect-square overflow-hidden rounded-xl ring-1 ring-ink-200/70">
                <video :src="preview.url" x-show="preview.isVideo" class="size-full object-cover" muted playsinline></video>
                <img :src="preview.url" x-show="!preview.isVideo" alt="" class="size-full object-cover">
                <span class="pointer-events-none absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-ink-950/70 to-transparent"></span>
                <span class="pointer-events-none absolute inset-x-1.5 bottom-1 truncate text-[10px] font-medium text-white" x-text="preview.name"></span>
                <button
                    type="button"
                    class="absolute right-1 top-1 inline-flex size-6 items-center justify-center rounded-full bg-ink-950/60 text-white transition hover:bg-red-600"
                    @click="removeFile(index)"
                    title="Remove file"
                    aria-label="Remove selected file"
                >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </li>
        </template>
    </ul>
</div>
