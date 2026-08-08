@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <x-page-header title="My Profile" subtitle="Manage your personal details, password, and see your recent activity." />

    @php
        $hasPasswordErrors = $errors->has('current_password') || $errors->has('password');
    @endphp

    <div
        x-data="{
            tab: '{{ $hasPasswordErrors ? 'security' : 'info' }}',
            passwordModalOpen: {{ $hasPasswordErrors ? 'true' : 'false' }},
            emailModalOpen: {{ $pendingEmail ? 'true' : 'false' }},
            phoneModalOpen: {{ $pendingPhone ? 'true' : 'false' }},
        }"
        class="grid gap-8 lg:grid-cols-[220px_1fr]"
    >
        {{-- Sidebar --}}
        <nav class="flex gap-1 overflow-x-auto pb-2 lg:flex-col lg:overflow-visible lg:pb-0" aria-label="Profile sections">
            <button type="button" @click="tab = 'info'" class="admin-sidebar-link shrink-0 lg:w-full" :class="tab === 'info' ? 'admin-sidebar-link-active' : ''">
                <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                Personal info
            </button>
            <button type="button" @click="tab = 'security'" class="admin-sidebar-link shrink-0 lg:w-full" :class="tab === 'security' ? 'admin-sidebar-link-active' : ''">
                <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                Security
            </button>
            <button type="button" @click="tab = 'orders'" class="admin-sidebar-link shrink-0 lg:w-full" :class="tab === 'orders' ? 'admin-sidebar-link-active' : ''">
                <x-icons.archive-box class="size-5 shrink-0" />
                Orders
            </button>
            <button type="button" @click="tab = 'cart'" class="admin-sidebar-link shrink-0 lg:w-full" :class="tab === 'cart' ? 'admin-sidebar-link-active' : ''">
                <x-icons.shopping-bag class="size-5 shrink-0" />
                Cart
            </button>
        </nav>

        {{-- Content --}}
        <div class="min-w-0">
            {{-- Personal info --}}
            <div x-show="tab === 'info'" x-cloak class="panel p-8">
                <h2 class="font-display text-lg font-bold uppercase tracking-wide text-ink-950">Personal details</h2>

                @if ($pendingEmail)
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-200/70 bg-amber-50 px-4 py-3">
                        <p class="text-sm text-amber-900">
                            Verification pending for <strong>{{ $pendingEmail }}</strong> — enter the code we emailed you to confirm the change.
                        </p>
                        <div class="flex shrink-0 items-center gap-3">
                            <button type="button" @click="emailModalOpen = true" class="text-xs font-bold uppercase tracking-wide text-accent-700 hover:underline">Verify now</button>
                            <form method="POST" action="{{ route('profile.email.cancel') }}">
                                @csrf
                                <button type="submit" class="text-xs font-bold uppercase tracking-wide text-ink-500 hover:underline">Cancel</button>
                            </form>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="first_name" class="form-label">First name</label>
                            <input type="text" id="first_name" name="first_name" class="form-input" value="{{ old('first_name', $user->first_name) }}" required>
                            @error('first_name')
                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="last_name" class="form-label">Last name</label>
                            <input type="text" id="last_name" name="last_name" class="form-input" value="{{ old('last_name', $user->last_name) }}" required>
                            @error('last_name')
                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="phone" class="form-label">Phone <span class="font-normal text-ink-400">(optional)</span></label>
                        <input type="tel" id="phone" name="phone" class="form-input" value="{{ old('phone', $user->phone) }}" placeholder="+383 44 123 456">
                        @error('phone')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        @if ($pendingPhone)
                            <div class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-200/70 bg-amber-50 px-4 py-3">
                                <p class="text-sm text-amber-900">
                                    Verification pending for <strong>{{ $pendingPhone }}</strong> — enter the code we emailed you to confirm it.
                                </p>
                                <div class="flex shrink-0 items-center gap-3">
                                    <button type="button" @click="phoneModalOpen = true" class="text-xs font-bold uppercase tracking-wide text-accent-700 hover:underline">Verify now</button>
                                    <form method="POST" action="{{ route('profile.phone.cancel') }}">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold uppercase tracking-wide text-ink-500 hover:underline">Cancel</button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="btn-primary px-8 py-3 text-sm">Save changes</button>
                    </div>
                </form>

                <div class="mt-8 border-t border-zinc-200/70 pt-6">
                    <label for="country" class="form-label">Country &amp; currency</label>
                    <p class="text-xs text-ink-500">Prices across the site are shown in this currency. All prices are set in EUR — other currencies are converted for display only, and you're always charged the EUR amount.</p>
                    <form method="POST" action="{{ route('country.update') }}" class="mt-2 max-w-xs">
                        @csrf
                        <select
                            name="country"
                            id="country"
                            onchange="this.form.requestSubmit()"
                            class="form-select w-full"
                        >
                            @foreach ($countries as $code => $info)
                                <option value="{{ $code }}" {{ $currentCountry === $code ? 'selected' : '' }}>
                                    {{ $info['label'] }} ({{ $info['currency'] }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            {{-- Security --}}
            <div x-show="tab === 'security'" x-cloak class="panel p-8">
                <h2 class="font-display text-lg font-bold uppercase tracking-wide text-ink-950">Security</h2>
                <p class="mt-2 max-w-md text-sm text-ink-500">Your password was last changed when you set it up or last updated it. Change it any time — you'll need your current password.</p>
                <button type="button" @click="$refs.passwordForm.reset(); passwordModalOpen = true" class="btn-primary mt-6 px-8 py-3 text-sm">Change password</button>
            </div>

            {{-- Orders --}}
            <div x-show="tab === 'orders'" x-cloak class="panel overflow-hidden p-0">
                <div class="flex items-center justify-between border-b border-zinc-200/70 px-8 py-6">
                    <h2 class="font-display text-lg font-bold uppercase tracking-wide text-ink-950">Recent orders</h2>
                    <a href="{{ route('orders.index') }}" class="link-brand text-sm">View all</a>
                </div>
                @if ($orders->isEmpty())
                    <p class="px-8 py-10 text-center text-sm text-ink-500">You haven't placed any orders yet.</p>
                @else
                    <ul class="divide-y divide-zinc-200/70">
                        @foreach ($orders as $order)
                            <li>
                                <a href="{{ route('orders.show', $order) }}" class="flex items-center justify-between gap-4 px-8 py-4 transition hover:bg-zinc-50">
                                    <div class="min-w-0">
                                        <p class="truncate font-mono text-xs font-semibold text-ink-900">{{ $order->order_number }}</p>
                                        <p class="mt-0.5 text-xs text-ink-500">{{ $order->created_at->format('M j, Y') }} · {{ $order->items_count }} {{ \Illuminate\Support\Str::plural('item', $order->items_count) }}</p>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-3">
                                        <x-admin.badge :tone="$order->status->tone()">{{ $order->status->label() }}</x-admin.badge>
                                        <span class="font-display text-sm font-semibold tabular-nums text-ink-950"><x-price :amount="$order->total" /></span>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Cart --}}
            <div x-show="tab === 'cart'" x-cloak class="panel overflow-hidden p-0">
                <div class="flex items-center justify-between border-b border-zinc-200/70 px-8 py-6">
                    <h2 class="font-display text-lg font-bold uppercase tracking-wide text-ink-950">In your cart</h2>
                    <a href="{{ route('cart.index') }}" class="link-brand text-sm">View cart</a>
                </div>
                @if ($cartLines->isEmpty())
                    <p class="px-8 py-10 text-center text-sm text-ink-500">Your cart is empty.</p>
                @else
                    <ul class="divide-y divide-zinc-200/70">
                        @foreach ($cartLines as $line)
                            @php
                                $v = $line['variant'];
                                $p = $v->product;
                                $thumb = $p->images->first();
                            @endphp
                            <li class="flex items-center gap-4 px-8 py-4">
                                <x-product-image-thumb :path="$thumb?->path" size="cartRow" />
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-ink-900">{{ $p->name }}</p>
                                    <p class="text-xs text-ink-500">{{ $v->size }} · {{ $v->color }} · Qty {{ $line['quantity'] }}</p>
                                </div>
                                <span class="shrink-0 font-display text-sm font-semibold tabular-nums text-ink-950"><x-price :amount="$line['line_total']" /></span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="flex items-center justify-between border-t border-zinc-200/70 px-8 py-4">
                        <span class="text-sm font-medium text-ink-600">Subtotal</span>
                        <span class="font-display text-base font-semibold tabular-nums text-ink-950"><x-price :amount="$cartSubtotal" /></span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Change password modal --}}
        <div
            class="fixed inset-0 z-50 pointer-events-none"
            x-cloak
            :class="{ 'pointer-events-auto': passwordModalOpen }"
            @keydown.escape.window="passwordModalOpen = false; $refs.passwordForm.reset()"
        >
            <div
                x-show="passwordModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-ink-950/50 backdrop-blur-sm"
                @click="passwordModalOpen = false; $refs.passwordForm.reset()"
            ></div>

            <div
                x-show="passwordModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute left-1/2 top-1/2 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl border border-zinc-200/90 bg-white p-8 shadow-elevated"
                @click.stop
            >
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-lg font-bold uppercase tracking-wide text-ink-950">Change password</h3>
                    <button type="button" @click="passwordModalOpen = false; $refs.passwordForm.reset()" class="inline-flex size-9 items-center justify-center rounded-xl text-ink-500 transition hover:bg-zinc-100 hover:text-ink-900" aria-label="Close">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form x-ref="passwordForm" method="POST" action="{{ route('profile.password.update') }}" class="mt-6 space-y-6">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="current_password" class="form-label">Current password</label>
                        <input type="password" id="current_password" name="current_password" class="form-input" autocomplete="current-password" required>
                        @error('current_password')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="form-label">New password</label>
                        <input type="password" id="password" name="password" class="form-input" autocomplete="new-password" required>
                        @error('password')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="form-label">Confirm new password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="btn-primary w-full py-3 text-sm">Update password</button>
                </form>
            </div>
        </div>

        {{-- Verify new email modal --}}
        <div
            class="fixed inset-0 z-50 pointer-events-none"
            x-cloak
            :class="{ 'pointer-events-auto': emailModalOpen }"
            @keydown.escape.window="emailModalOpen = false"
        >
            <div
                x-show="emailModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-ink-950/50 backdrop-blur-sm"
                @click="emailModalOpen = false"
            ></div>

            <div
                x-show="emailModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute left-1/2 top-1/2 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl border border-zinc-200/90 bg-white p-8 shadow-elevated"
                @click.stop
            >
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-lg font-bold uppercase tracking-wide text-ink-950">Verify new email</h3>
                    <button type="button" @click="emailModalOpen = false" class="inline-flex size-9 items-center justify-center rounded-xl text-ink-500 transition hover:bg-zinc-100 hover:text-ink-900" aria-label="Close">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                @if ($pendingEmail)
                    <p class="mt-2 text-sm text-ink-500">Enter the 6-digit code we sent to <strong>{{ $pendingEmail }}</strong>.</p>
                @endif
                <form method="POST" action="{{ route('profile.email.verify') }}" class="mt-6 space-y-6">
                    @csrf
                    <div>
                        <label for="email_code" class="form-label">Verification code</label>
                        <input type="text" id="email_code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" class="form-input text-center font-mono text-lg tracking-[0.5em]" required autofocus>
                        @error('code')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="btn-primary w-full py-3 text-sm">Confirm change</button>
                </form>
                <div class="mt-4 flex items-center justify-between">
                    <form method="POST" action="{{ route('profile.email.resend') }}">
                        @csrf
                        <button type="submit" class="text-xs font-bold uppercase tracking-wide text-accent-700 hover:underline">Resend code</button>
                    </form>
                    <form method="POST" action="{{ route('profile.email.cancel') }}">
                        @csrf
                        <button type="submit" class="text-xs font-bold uppercase tracking-wide text-ink-500 hover:underline">Cancel change</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Verify new phone modal --}}
        <div
            class="fixed inset-0 z-50 pointer-events-none"
            x-cloak
            :class="{ 'pointer-events-auto': phoneModalOpen }"
            @keydown.escape.window="phoneModalOpen = false"
        >
            <div
                x-show="phoneModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-ink-950/50 backdrop-blur-sm"
                @click="phoneModalOpen = false"
            ></div>

            <div
                x-show="phoneModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute left-1/2 top-1/2 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl border border-zinc-200/90 bg-white p-8 shadow-elevated"
                @click.stop
            >
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-lg font-bold uppercase tracking-wide text-ink-950">Verify phone number</h3>
                    <button type="button" @click="phoneModalOpen = false" class="inline-flex size-9 items-center justify-center rounded-xl text-ink-500 transition hover:bg-zinc-100 hover:text-ink-900" aria-label="Close">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                @if ($pendingPhone)
                    <p class="mt-2 text-sm text-ink-500">Enter the 6-digit code we emailed you to confirm <strong>{{ $pendingPhone }}</strong>.</p>
                @endif
                <form method="POST" action="{{ route('profile.phone.verify') }}" class="mt-6 space-y-6">
                    @csrf
                    <div>
                        <label for="phone_code" class="form-label">Verification code</label>
                        <input type="text" id="phone_code" name="phone_code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" class="form-input text-center font-mono text-lg tracking-[0.5em]" required autofocus>
                        @error('phone_code')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="btn-primary w-full py-3 text-sm">Confirm phone number</button>
                </form>
                <div class="mt-4 flex items-center justify-between">
                    <form method="POST" action="{{ route('profile.phone.resend') }}">
                        @csrf
                        <button type="submit" class="text-xs font-bold uppercase tracking-wide text-accent-700 hover:underline">Resend code</button>
                    </form>
                    <form method="POST" action="{{ route('profile.phone.cancel') }}">
                        @csrf
                        <button type="submit" class="text-xs font-bold uppercase tracking-wide text-ink-500 hover:underline">Cancel change</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
