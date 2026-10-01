<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

class InternalCronController extends Controller
{
    public function expireAbandonedOrders(Request $request): Response
    {
        $secret = (string) config('services.internal_cron.secret');

        if ($secret === '' || ! hash_equals($secret, (string) $request->query('secret'))) {
            abort(404);
        }

        Artisan::call('orders:expire-abandoned-card-payments');

        return response(Artisan::output(), 200)->header('Content-Type', 'text/plain');
    }
}
