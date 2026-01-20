<?php

namespace App\Console\Commands;

use App\Services\PesapalService;
use Illuminate\Console\Command;

class RegisterPesapalIpn extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pesapal:register-ipn 
                            {--url= : The IPN URL to register}
                            {--type=GET : The notification type (GET or POST)}';

    /**
     * The console command description.
     */
    protected $description = 'Register IPN URL with Pesapal for payment notifications';

    /**
     * Execute the console command.
     */
    public function handle(PesapalService $pesapalService): int
    {
        $url = $this->option('url') ?: config('services.pesapal.ipn_url');
        $type = $this->option('type');

        if (!$url) {
            $this->error('IPN URL is required. Provide it via --url option or set PESAPAL_IPN_URL in .env');
            return Command::FAILURE;
        }

        $this->info("Registering IPN URL: {$url}");
        $this->info("Notification type: {$type}");

        $result = $pesapalService->registerIpnUrl($url, $type);

        if ($result['success']) {
            $this->info('IPN URL registered successfully!');
            $this->table(
                ['Field', 'Value'],
                [
                    ['IPN ID', $result['ipn_id']],
                    ['URL', $result['url']],
                    ['Type', $result['ipn_notification_type']],
                    ['Status', $result['ipn_status']],
                    ['Created', $result['created_date']]
                ]
            );

            $this->warn('Important: Save the IPN ID in your environment file:');
            $this->line("PESAPAL_DEFAULT_NOTIFICATION_ID={$result['ipn_id']}");

            return Command::SUCCESS;
        } else {
            $this->error('Failed to register IPN URL: ' . $result['error']);
            return Command::FAILURE;
        }
    }
}