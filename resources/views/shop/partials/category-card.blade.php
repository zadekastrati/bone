<a
    href="{{ route('shop.category', $category) }}"
    class="store-card-interactive group flex h-full flex-col rounded-2xl p-6 sm:p-7"
>
    <span class="text-xs font-bold uppercase tracking-mega text-accent-600">
        {{ $category->active_products_count }} {{ $category->active_products_count === 1 ? 'product' : 'products' }}
    </span>
    <span class="font-display mt-3 text-lg font-bold uppercase tracking-wide text-ink-950 group-hover:text-accent-700">
        {{ $category->name }}
    </span>
    @if ($category->description)
        <p class="mt-2 flex-1 text-sm leading-relaxed text-ink-600 text-pretty">{{ \Illuminate\Support\Str::limit($category->description, 120) }}</p>
    @endif
    <span class="mt-5 inline-flex items-center gap-1 text-xs font-bold uppercase tracking-mega text-accent-700 transition group-hover:gap-2">
        View products
        <span aria-hidden="true">→</span>
    </span>
</a>
