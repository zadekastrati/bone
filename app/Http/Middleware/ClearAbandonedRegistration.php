<?php

namespace App\Http\Middleware;

use App\Services\RegistrationOtpService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A "register_pending" session entry should only survive while the user is
 * actually inside the join flow. Any request to a route outside that flow
 * means they left, so the pending registration (and its OTP) is destroyed —
 * returning to /register later always starts from the beginning.
 */
class ClearAbandonedRegistration
{
    private const FLOW_ROUTES = [
        'register',
        'register.store',
        'register.verify',
        'register.verify.store',
        'register.resend',
        'register.cancel',
    ];

    public function __construct(
        private readonly RegistrationOtpService $registrationOtp
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $pending = $request->session()->get('register_pending');

        if ($pending !== null && ! in_array($request->route()?->getName(), self::FLOW_ROUTES, true)) {
            if (isset($pending['email'])) {
                $this->registrationOtp->forget($pending['email']);
            }

            $request->session()->forget('register_pending');
        }

        return $next($request);
    }
}
