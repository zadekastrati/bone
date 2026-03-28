<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted proxies
    |--------------------------------------------------------------------------
    |
    | Behind nginx, Cloudflare, or a load balancer, set TRUSTED_PROXIES to "*"
    | so Laravel trusts X-Forwarded-* headers (HTTPS, host, client IP).
    | Use comma-separated IPs instead of * if you can list your proxy only.
    | Leave unset for local development without forwarded headers.
    |
    */

    'proxies' => env('TRUSTED_PROXIES') ?: null,

];
