<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\VerifyEmailChangeRequest;
use App\Http\Requests\Profile\VerifyPhoneChangeRequest;
use App\Mail\EmailChangedMail;
use App\Mail\EmailChangeOtpMail;
use App\Mail\EmailChangeRequestedMail;
use App\Mail\PasswordChangedMail;
use App\Mail\PhoneChangeOtpMail;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CurrencyService;
use App\Services\EmailChangeOtpService;
use App\Services\PhoneChangeOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CurrencyService $currency,
        private readonly EmailChangeOtpService $emailChangeOtp,
        private readonly PhoneChangeOtpService $phoneChangeOtp
    ) {}

    public function edit(Request $request): View
    {
        $user = $request->user();

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->withCount('items')
            ->with(['items.variant.product' => fn ($q) => $q->withTrashed()->with('images')])
            ->latest()
            ->take(10)
            ->get();

        $cartLines = $this->cart->lines();
        $cartSubtotal = $this->cart->subtotal();

        $pendingEmail = $this->activePendingEmail($request);
        $pendingPhone = $this->activePendingPhone($request);

        $countries = $this->currency->countries();
        $currentCountry = $this->currency->currentCountry();

        return view('profile.edit', compact(
            'user',
            'orders',
            'cartLines',
            'cartSubtotal',
            'pendingEmail',
            'pendingPhone',
            'countries',
            'currentCountry'
        ));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $appName = (string) config('app.name');

        $newPhone = $validated['phone'] ?? null;
        $oldPhone = $user->phone;
        $phoneNeedsVerification = $newPhone !== null && $newPhone !== '' && $newPhone !== $oldPhone;

        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            // Clearing the number, or leaving it unchanged, applies right away.
            // Setting a new one is deferred until it's verified below.
            'phone' => $phoneNeedsVerification ? $oldPhone : $newPhone,
        ]);

        if ($phoneNeedsVerification) {
            $code = $this->phoneChangeOtp->issue($user->id);

            Mail::to($user->email)->locale(app()->getLocale())->send(new PhoneChangeOtpMail(
                code: $code,
                userName: $user->name,
                appName: $appName,
                newPhone: $newPhone,
            ));

            $request->session()->put('phone_change_pending', ['phone' => $newPhone]);
        }

        $newEmail = $validated['email'];
        $oldEmail = $user->email;
        $emailNeedsVerification = $newEmail !== $oldEmail;

        if (! $emailNeedsVerification) {
            return redirect()
                ->route('profile.edit')
                ->with('success', $phoneNeedsVerification
                    ? __('Your details were updated. We sent a verification code to your email — enter it below to confirm your new phone number.')
                    : __('Profile updated.'));
        }

        $requestedAt = now()->format('M j, Y \a\t g:i A');

        $code = $this->emailChangeOtp->issue($newEmail);

        Mail::to($newEmail)->locale(app()->getLocale())->send(new EmailChangeOtpMail(
            code: $code,
            userName: $user->name,
            appName: $appName,
        ));

        // Let the current address holder know too, in case this wasn't them.
        Mail::to($oldEmail)->locale(app()->getLocale())->send(new EmailChangeRequestedMail(
            userName: $user->name,
            appName: $appName,
            newEmail: $newEmail,
            requestedAt: $requestedAt,
        ));

        $request->session()->put('email_change_pending', ['email' => $newEmail]);

        return redirect()
            ->route('profile.edit')
            ->with('success', __('Your details were updated. We sent a verification code to your new email — enter it below to confirm the change.'));
    }

    public function verifyEmail(VerifyEmailChangeRequest $request): RedirectResponse
    {
        $pending = $this->activePendingEmail($request);
        if ($pending === null) {
            return redirect()
                ->route('profile.edit')
                ->with('error', __('That verification code expired. Please request the email change again.'));
        }

        if (! $this->emailChangeOtp->verify($pending, $request->validated('code'))) {
            throw ValidationException::withMessages([
                'code' => __('Invalid or expired code. Try again or request a new code.'),
            ]);
        }

        $this->emailChangeOtp->forget($pending);
        $request->session()->forget('email_change_pending');

        $user = $request->user();
        $user->update(['email' => $pending]);

        Mail::to($pending)->locale(app()->getLocale())->send(new EmailChangedMail(
            userName: $user->name,
            appName: (string) config('app.name'),
            changedAt: now()->format('M j, Y \a\t g:i A'),
        ));

        return redirect()
            ->route('profile.edit')
            ->with('success', __('Your email address has been updated.'));
    }

    public function resendEmailCode(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('email_change_pending');
        if (! isset($pending['email'])) {
            return redirect()->route('profile.edit');
        }

        $email = $pending['email'];
        $code = $this->emailChangeOtp->issue($email);

        Mail::to($email)->locale(app()->getLocale())->send(new EmailChangeOtpMail(
            code: $code,
            userName: $request->user()->name,
            appName: (string) config('app.name'),
        ));

        return redirect()
            ->route('profile.edit')
            ->with('success', __('A new code has been sent to your new email.'));
    }

    public function cancelEmailChange(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('email_change_pending');
        if (isset($pending['email'])) {
            $this->emailChangeOtp->forget($pending['email']);
        }

        $request->session()->forget('email_change_pending');

        return redirect()
            ->route('profile.edit')
            ->with('success', __('Email change cancelled.'));
    }

    public function verifyPhone(VerifyPhoneChangeRequest $request): RedirectResponse
    {
        $user = $request->user();
        $pending = $this->activePendingPhone($request);

        if ($pending === null) {
            return redirect()
                ->route('profile.edit')
                ->with('error', __('That verification code expired. Please request the phone change again.'));
        }

        if (! $this->phoneChangeOtp->verify($user->id, $request->validated('phone_code'))) {
            throw ValidationException::withMessages([
                'code' => __('Invalid or expired code. Try again or request a new code.'),
            ]);
        }

        $this->phoneChangeOtp->forget($user->id);
        $request->session()->forget('phone_change_pending');

        $user->update(['phone' => $pending]);

        return redirect()
            ->route('profile.edit')
            ->with('success', __('Your phone number has been updated.'));
    }

    public function resendPhoneCode(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('phone_change_pending');
        if (! isset($pending['phone'])) {
            return redirect()->route('profile.edit');
        }

        $user = $request->user();
        $code = $this->phoneChangeOtp->issue($user->id);

        Mail::to($user->email)->locale(app()->getLocale())->send(new PhoneChangeOtpMail(
            code: $code,
            userName: $user->name,
            appName: (string) config('app.name'),
            newPhone: $pending['phone'],
        ));

        return redirect()
            ->route('profile.edit')
            ->with('success', __('A new code has been sent to your email.'));
    }

    public function cancelPhoneChange(Request $request): RedirectResponse
    {
        $this->phoneChangeOtp->forget($request->user()->id);
        $request->session()->forget('phone_change_pending');

        return redirect()
            ->route('profile.edit')
            ->with('success', __('Phone number change cancelled.'));
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'password' => $request->validated('password'),
        ]);

        Mail::to($user->email)->locale(app()->getLocale())->send(new PasswordChangedMail(
            userName: $user->name,
            appName: (string) config('app.name'),
            changedAt: now()->format('M j, Y \a\t g:i A'),
        ));

        return redirect()
            ->route('profile.edit')
            ->with('success', __('Password changed.'));
    }

    /**
     * The pending new email, or null if there isn't one or its code expired
     * — clearing stale session state either way.
     */
    private function activePendingEmail(Request $request): ?string
    {
        $pending = $request->session()->get('email_change_pending');

        if ($pending === null || ! isset($pending['email'])) {
            return null;
        }

        if (! $this->emailChangeOtp->hasActiveCode($pending['email'])) {
            $request->session()->forget('email_change_pending');

            return null;
        }

        return $pending['email'];
    }

    /**
     * The pending new phone number, or null if there isn't one or its code
     * expired — clearing stale session state either way.
     */
    private function activePendingPhone(Request $request): ?string
    {
        $pending = $request->session()->get('phone_change_pending');

        if ($pending === null || ! isset($pending['phone'])) {
            return null;
        }

        if (! $this->phoneChangeOtp->hasActiveCode($request->user()->id)) {
            $request->session()->forget('phone_change_pending');

            return null;
        }

        return $pending['phone'];
    }
}
