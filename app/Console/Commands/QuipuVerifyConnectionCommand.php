<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class QuipuVerifyConnectionCommand extends Command
{
    protected $signature = 'quipu:verify-connection';

    protected $description = 'Verify mTLS connectivity to the Quipu/ProCredit card payment gateway by creating a throwaway €0.01 test order';

    public function handle(): int
    {
        $config = config('services.quipu');

        foreach (['merchant_id', 'order_endpoint', 'cert_path', 'key_path', 'ca_path'] as $key) {
            if (empty($config[$key])) {
                $this->error("Missing config: services.quipu.{$key} (set QUIPU_".strtoupper($key)." in .env)");

                return self::FAILURE;
            }
        }

        foreach (['cert_path' => $config['cert_path'], 'key_path' => $config['key_path'], 'ca_path' => $config['ca_path']] as $label => $relativePath) {
            if (! is_readable(base_path($relativePath))) {
                $this->error("Cannot read {$label}: ".base_path($relativePath));

                return self::FAILURE;
            }
        }

        $this->info('Merchant ID: '.$config['merchant_id']);
        $this->info('Endpoint: '.$config['order_endpoint']);
        $this->info('Creating a throwaway €0.01 test order to verify the mTLS handshake…');

        try {
            $response = Http::withOptions([
                'cert' => base_path($config['cert_path']),
                'ssl_key' => base_path($config['key_path']),
                'verify' => base_path($config['ca_path']),
                'timeout' => 15,
            ])->post($config['order_endpoint'], [
                'order' => [
                    'typeRid' => '1',
                    'amount' => '0.01',
                    'currency' => 'EUR',
                    'description' => 'Connectivity check ('.config('app.env').')',
                    'language' => 'en',
                    'hppRedirectUrl' => config('app.url'),
                    'initiationEnvKind' => 'Browser',
                    'consumerDevice' => [
                        'browser' => [
                            'javaEnabled' => false,
                            'jsEnabled' => true,
                            'acceptHeader' => 'application/json,application/jose;charset=utf-8',
                            'ip' => '127.0.0.1',
                            'colorDepth' => '24',
                            'screenW' => '1080',
                            'screenH' => '1920',
                            'tzOffset' => '0',
                            'language' => 'en-EN',
                            'userAgent' => 'quipu:verify-connection',
                        ],
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            $this->error('Request failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->line('HTTP status: '.$response->status());
        $this->line('Body: '.$response->body());

        if (! $response->successful() || ! isset($response->json()['order']['id'])) {
            $this->error('Connection reached the gateway but the response was not a successful order creation — see body above.');

            return self::FAILURE;
        }

        $this->info('✓ mTLS handshake accepted, order created successfully. Certificates and Merchant ID are valid.');

        return self::SUCCESS;
    }
}
