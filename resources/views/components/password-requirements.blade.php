{{--
    Live password-requirement checklist. Must be placed inside an x-data
    scope that exposes hasMinLength/hasUpper/hasLower/hasNumber/hasSymbol
    booleans (see resources/views/auth/register.blade.php).
--}}
<ul class="mt-2 space-y-0.5 text-xs" aria-live="polite">
    <li :class="hasMinLength ? 'text-green-600' : 'text-ink-500'">
        <span x-text="hasMinLength ? '✓' : '•'"></span> {{ __('At least 8 characters') }}
    </li>
    <li :class="hasUpper ? 'text-green-600' : 'text-ink-500'">
        <span x-text="hasUpper ? '✓' : '•'"></span> {{ __('At least one uppercase letter') }}
    </li>
    <li :class="hasLower ? 'text-green-600' : 'text-ink-500'">
        <span x-text="hasLower ? '✓' : '•'"></span> {{ __('At least one lowercase letter') }}
    </li>
    <li :class="hasNumber ? 'text-green-600' : 'text-ink-500'">
        <span x-text="hasNumber ? '✓' : '•'"></span> {{ __('At least one number') }}
    </li>
    <li :class="hasSymbol ? 'text-green-600' : 'text-ink-500'">
        <span x-text="hasSymbol ? '✓' : '•'"></span> {{ __('At least one special character') }}
    </li>
</ul>
