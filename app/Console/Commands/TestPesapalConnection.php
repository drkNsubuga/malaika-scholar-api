<?php

namespace App\Console\Commands;

use App\Services\PesapalService;
use Illuminate\Console\Command;

class TestPesapalConnection extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pesapal:test-connection';

    /**
     * The console command description.
     */
    protected $description = 'Test connection to Pesapal API and validate configuration';

    /**
     * Execute the console command.
     */
    public function handle(PesapalService $pesapalService): int
    {
        $this->info('Testing Pesapal API connection...');

        // Check configuration
        $this->checkConfiguration();

        // Test authentication
        $this->info('Testing authentication...');
        $authResult = $pesapalService->authenticate();

        if ($authResult['success']) {
            $this->info('✓ Authentication successful');
            $this->line("Token expires at: {$authResult['expires_at']}");
        } else {
            $this->error('✗ Authentication failed: ' . $authResult['error']);
            return Command::FAILURE;
        }

        // Test order submission (dry run)
        $this->info('Testing order submission (dry run)...');
        $testOrder = [
            'merchant_reference' => 'TEST_' . time(),
            'amount' => 100,
            'currency' => 'KES',
            'description' => 'Test payment for API connectivity',
            'callback_url' => config('services.pesapal.callback_url'),
            'customer_email' => 'test@example.com',
            'customer_phone' => '+254700000000',
            'customer_first_name' => 'Test',
            'customer_last_name' => 'User',
            'country_code' => 'KE'
        ];

        $orderResult = $pesapalService->submitOrderRequest($testOrder);

        if ($orderResult['success']) {
            $this->info('✓ Order submission test successful');
            $this->line("Order tracking ID: {$orderResult['order_tracking_id']}");
            
            if (isset($orderResult['redirect_url'])) {
                $this->line("Redirect URL: {$orderResult['redirect_url']}");
            }
        } else {
            $this->error('✗ Order submission test failed: ' . $orderResult['error']);
            return Command::FAILURE;
        }

        $this->info('All tests passed! Pesapal integration is working correctly.');
        return Command::SUCCESS;
    }

    /**
     * Check Pesapal configuration
     */
    private function checkConfiguration(): void
    {
        $this->info('Checking configuration...');

        $config = config('services.pesapal');
        $required = ['consumer_key', 'consumer_secret', 'callback_url', 'ipn_url'];
        $missing = [];

        foreach ($required as $key) {
            if (empty($config[$key])) {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            $this->error('Missing required configuration:');
            foreach ($missing as $key) {
                $this->line("  - {$key}");
            }
            $this->line('');
            $this->line('Please set the following environment variables:');
            $this->line('PESAPAL_CONSUMER_KEY=your_consumer_key');
            $this->line('PESAPAL_CONSUMER_SECRET=your_consumer_secret');
            $this->line('PESAPAL_CALLBACK_URL=your_callback_url');
            $this->line('PESAPAL_IPN_URL=your_ipn_url');
            exit(1);
        }

        $this->info('✓ Configuration looks good');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Environment', $config['environment']],
                ['Consumer Key', substr($config['consumer_key'], 0, 8) . '...'],
                ['Consumer Secret', substr($config['consumer_secret'], 0, 8) . '...'],
                ['Callback URL', $config['callback_url']],
                ['IPN URL', $config['ipn_url']],
                ['Default Currency', $config['default_currency']],
            ]
        );
    }
}