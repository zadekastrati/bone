{{--
    Each image's delete <form> lives here, outside the main product-edit
    <form>, because nesting a <form> inside another <form> is invalid HTML —
    browsers close the outer form early when they hit the first nested one,
    which silently detached the "Save changes" button from its form. The
    delete button stays visually in place on its image tile via the standard
    form="..." attribute, which associates a button with a form anywhere in
    the document, not just an ancestor.
--}}
@foreach ($product->images as $img)
    @php
        $sectionKey = $img->color !== null ? \Illuminate\Support\Str::slug($img->color) : 'all';
    @endphp
    <form
        id="delete-image-form-{{ $img->id }}"
        method="POST"
        action="{{ route('admin.products.images.destroy', [$product, $img]) }}"
        data-confirm="Are you sure you want to delete this photo?"
        data-confirm-label="Delete"
        class="hidden"
        onsubmit="
            if (this.dataset.confirmed !== 'true') { return; }
            event.preventDefault();
            const container = document.getElementById('product-image-gallery-{{ $sectionKey }}');
            axios.delete(this.action)
                .then((response) => { if (container) { container.outerHTML = response.data; } })
                .catch(() => { alert('Failed to delete image. Please try again.'); });
        "
    >
        @csrf
        @method('DELETE')
    </form>
@endforeach
