<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyOtpCodeRequest;
use App\Mail\RegisterOtpMail;
use App\Models\User;
use App\Services\RegistrationOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(
        private readonly RegistrationOtpService $registrationOtp
    ) {}

    public function create(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        if ($this->activePending($request) !== null) {
            return redirect()->route('register.verify');
        }

        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $code = $this->registrationOtp->issue($validated['email']);

        Mail::to($validated['email'])->locale(app()->getLocale())->send(new RegisterOtpMail(
            code: $code,
            userName: trim($validated['first_name'].' '.$validated['last_name']),
            appName: (string) config('app.name'),
        ));

        $request->session()->put('register_pending', [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password_enc' => Crypt::encryptString($validated['password']),
        ]);

        return redirect()
            ->route('register.verify')
            ->with('success', __('We sent a 6-digit code to your email. Enter it below to finish creating your account.'));
    }

    public function showVerify(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        $pending = $this->activePending($request);

        if ($pending === null) {
            return redirect()
                ->route('register')
                ->with('error', __('Start registration from the register page first.'));
        }

        return view('auth.register-verify', [
            'emailMasked' => $this->maskEmail($pending['email']),
        ]);
    }

    public function verify(VerifyOtpCodeRequest $request): RedirectResponse
    {
        $pending = $request->session()->get('register_pending');
        if ($pending === null || ! isset($pending['email'], $pending['first_name'], $pending['last_name'], $pending['password_enc'])) {
            return redirect()
                ->route('register')
                ->with('error', __('Session expired. Please register again.'));
        }

        $email = $pending['email'];

        if (! $this->registrationOtp->hasActiveCode($email)) {
            $request->session()->forget('register_pending');

            return redirect()
                ->route('register')
                ->with('error', __('Your verification code expired. Please start again.'));
        }

        if (! $this->registrationOtp->verify($email, $request->validated('code'))) {
            throw ValidationException::withMessages([
                'code' => __('Invalid or expired code. Try again or request a new code.'),
            ]);
        }

        if (User::query()->where('email', $email)->exists()) {
            $this->registrationOtp->forget($email);
            $request->session()->forget('register_pending');

            return redirect()
                ->route('login')
                ->with('error', __('An account with this email already exists. Please log in.'));
        }

        $this->registrationOtp->forget($email);

        $password = Crypt::decryptString($pending['password_enc']);

        $user = User::create([
            'first_name' => $pending['first_name'],
            'last_name' => $pending['last_name'],
            'email' => $email,
            'password' => $password,
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $request->session()->forget('register_pending');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('home')
            ->with('success', __('Account created successfully.'));
    }

    public function resend(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('register_pending');
        if ($pending === null || ! isset($pending['email'], $pending['first_name'], $pending['last_name'])) {
            return redirect()->route('register');
        }

        $email = $pending['email'];

        $code = $this->registrationOtp->issue($email);

        Mail::to($email)->locale(app()->getLocale())->send(new RegisterOtpMail(
            code: $code,
            userName: trim($pending['first_name'].' '.$pending['last_name']),
            appName: (string) config('app.name'),
        ));

        return back()->with('success', __('A new code has been sent to your email.'));
    }

    public function cancel(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('register_pending');
        if (isset($pending['email'])) {
            $this->registrationOtp->forget($pending['email']);
        }

        $request->session()->forget('register_pending');

        return redirect()
            ->route('register')
            ->with('success', __('Registration cancelled. You can start again anytime.'));
    }

    /**
     * The pending registration payload, or null if there isn't one or its
     * code has expired — clearing the stale session state either way so a
     * user who left and came back lands on the start page, not verify.
     *
     * @return array{first_name: string, last_name: string, email: string, password_enc: string}|null
     */
    private function activePending(Request $request): ?array
    {
        $pending = $request->session()->get('register_pending');

        if ($pending === null || ! isset($pending['email'])) {
            return null;
        }

        if (! $this->registrationOtp->hasActiveCode($pending['email'])) {
            $request->session()->forget('register_pending');

            return null;
        }

        return $pending;
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
