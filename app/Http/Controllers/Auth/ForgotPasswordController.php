<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\NewPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpCodeRequest;
use App\Mail\PasswordResetOtpMail;
use App\Models\User;
use App\Services\PasswordResetOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private readonly PasswordResetOtpService $passwordResetOtp
    ) {}

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('password_reset_pending')) {
            return redirect()->route('password.reset.verify');
        }

        return view('auth.forgot-password');
    }

    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        $email = $request->validated('email');

        $user = User::query()->where('email', $email)->first();

        if ($user !== null) {
            $code = $this->passwordResetOtp->issue($email);

            Mail::to($email)->locale(app()->getLocale())->send(new PasswordResetOtpMail(
                code: $code,
                userName: $user->name,
                appName: (string) config('app.name'),
            ));
        }

        $request->session()->put('password_reset_pending', [
            'email' => $email,
            'verified' => false,
        ]);

        return redirect()
            ->route('password.reset.verify')
            ->with('success', __('If an account exists for that email, we\'ve sent a 6-digit code. Enter it below to continue.'));
    }

    public function showVerify(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('password_reset_pending')) {
            return redirect()
                ->route('password.reset')
                ->with('error', __('Start a password reset from the previous page first.'));
        }

        $pending = $request->session()->get('password_reset_pending');

        return view('auth.reset-password', [
            'emailMasked' => $this->maskEmail($pending['email']),
        ]);
    }

    public function verify(VerifyOtpCodeRequest $request): RedirectResponse
    {
        $pending = $request->session()->get('password_reset_pending');
        if ($pending === null || ! isset($pending['email'])) {
            return redirect()
                ->route('password.reset')
                ->with('error', __('Session expired. Please start again.'));
        }

        $email = $pending['email'];

        if (! $this->passwordResetOtp->verify($email, $request->validated('code'))) {
            throw ValidationException::withMessages([
                'code' => __('Invalid or expired code. Try again or request a new code.'),
            ]);
        }

        $this->passwordResetOtp->forget($email);

        $request->session()->put('password_reset_pending', [
            'email' => $email,
            'verified' => true,
        ]);

        return redirect()
            ->route('password.reset.password')
            ->with('success', __('Code verified. Choose a new password.'));
    }

    public function showNewPassword(Request $request): View|RedirectResponse
    {
        $pending = $request->session()->get('password_reset_pending');
        if ($pending === null || empty($pending['verified'])) {
            return redirect()
                ->route('password.reset')
                ->with('error', __('Please verify your code first.'));
        }

        return view('auth.reset-password-new', [
            'emailMasked' => $this->maskEmail($pending['email']),
        ]);
    }

    public function updatePassword(NewPasswordRequest $request): RedirectResponse
    {
        $pending = $request->session()->get('password_reset_pending');
        if ($pending === null || empty($pending['verified']) || ! isset($pending['email'])) {
            return redirect()
                ->route('password.reset')
                ->with('error', __('Session expired. Please start again.'));
        }

        $user = User::query()->where('email', $pending['email'])->first();
        if ($user === null) {
            $request->session()->forget('password_reset_pending');

            return redirect()
                ->route('password.reset')
                ->with('error', __('Something went wrong. Please start again.'));
        }

        $user->update(['password' => Hash::make($request->validated('password'))]);

        $request->session()->forget('password_reset_pending');

        return redirect()
            ->route('login')
            ->with('success', __('Your password has been updated. Log in with your new password.'));
    }

    public function resend(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('password_reset_pending');
        if ($pending === null || ! isset($pending['email'])) {
            return redirect()->route('password.reset');
        }

        $email = $pending['email'];
        $user = User::query()->where('email', $email)->first();

        if ($user !== null) {
            $code = $this->passwordResetOtp->issue($email);

            Mail::to($email)->locale(app()->getLocale())->send(new PasswordResetOtpMail(
                code: $code,
                userName: $user->name,
                appName: (string) config('app.name'),
            ));
        }

        return back()->with('success', __('If an account exists for that email, a new code has been sent.'));
    }

    public function cancel(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('password_reset_pending');
        if (isset($pending['email'])) {
            $this->passwordResetOtp->forget($pending['email']);
        }

        $request->session()->forget('password_reset_pending');

        return redirect()
            ->route('password.reset')
            ->with('success', __('Password reset cancelled. You can start again anytime.'));
    }

    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email, 2);
        if (count($parts) !== 2) {
            return '***';
        }

        [$local, $domain] = $parts;
        $len = strlen($local);
        if ($len <= 2) {
            return str_repeat('*', $len).'@'.$domain;
        }

        return substr($local, 0, 1)
            .str_repeat('*', max(1, $len - 2))
            .substr($local, -1)
            .'@'
            .$domain;
    }
}
