@props(['class' => ''])

<form method="POST" action="{{ route('locale.update') }}" class="{{ $class }}">
    @csrf
    <label for="store-locale-select" class="sr-only">{{ __('Language') }}</label>
    <select
        name="locale"
        id="store-locale-select"
        onchange="this.form.requestSubmit()"
        class="cursor-pointer appearance-none rounded-full border border-zinc-200/80 bg-white/70 py-1.5 pl-3 pr-7 text-xs font-medium text-ink-700 transition-colors hover:border-zinc-300 hover:bg-white focus:outline-none focus:ring-2 focus:ring-accent-500/30"
        style="background-image:url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%2373655a%22 stroke-width=%221.5%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 d=%22m19.5 8.25-7.5 7.5-7.5-7.5%22/%3E%3C/svg%3E');background-repeat:no-repeat;background-position:right 0.4rem center;background-size:0.9em;"
    >
        @foreach (config('app.available_locales', ['en' => 'English']) as $code => $label)
            <option value="{{ $code }}" {{ app()->getLocale() === $code ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
</form>
