<?php

namespace App\Console\Commands;

use App\Services\StorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class StorageInfo extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'storage:info 
                            {--test : Test connectivity to all configured storage disks}
                            {--disk= : Show info for specific disk only}';

    /**
     * The console command description.
     */
    protected $description = 'Display storage configuration and test connectivity';

    /**
     * Execute the console command.
     */
    public function handle(StorageService $storageService): int
    {
        $specificDisk = $this->option('disk');
        $testConnectivity = $this->option('test');
        
        $this->info('Storage Configuration Information');
        $this->line('=====================================');
        
        if ($specificDisk) {
            $this->showDiskInfo($specificDisk, $testConnectivity);
        } else {
            $this->showAllDisksInfo($testConnectivity);
        }
        
        if ($testConnectivity) {
            $this->line('');
            $this->info('Connectivity Test Results');
            $this->line('========================');
            $this->testStorageConnectivity($specificDisk);
        }
        
        return Command::SUCCESS;
    }

    /**
     * Show information for all configured disks
     */
    private function showAllDisksInfo(bool $testConnectivity): void
    {
        $storageInfo = app(StorageService::class)->getStorageInfo();
        
        $this->table(
            ['Setting', 'Value'],
            [
                ['Default Disk', $storageInfo['default_disk']],
                ['Documents Driver', $storageInfo['documents_driver']],
                ['Avatars Driver', $storageInfo['avatars_driver']],
                ['Backup Driver', $storageInfo['backup_driver']],
            ]
        );
        
        $this->line('');
        $this->info('Available Disks:');
        
        $diskData = [];
        foreach ($storageInfo['available_disks'] as $diskName) {
            $config = config("filesystems.disks.{$diskName}");
            $diskData[] = [
                $diskName,
                $config['driver'] ?? 'unknown',
                $this->getDiskStatus($diskName, $testConnectivity)
            ];
        }
        
        $this->table(['Disk Name', 'Driver', 'Status'], $diskData);
    }

    /**
     * Show information for a specific disk
     */
    private function showDiskInfo(string $diskName, bool $testConnectivity): void
    {
        $config = config("filesystems.disks.{$diskName}");
        
        if (!$config) {
            $this->error("Disk '{$diskName}' not found in configuration.");
            return;
        }
        
        $this->info("Configuration for disk: {$diskName}");
        $this->line('');
        
        $configData = [];
        foreach ($config as $key => $value) {
            // Hide sensitive information
            if (in_array($key, ['key', 'secret', 'password'])) {
                $value = $value ? str_repeat('*', 8) : 'not set';
            }
            
            $configData[] = [$key, is_array($value) ? json_encode($value) : (string) $value];
        }
        
        $this->table(['Setting', 'Value'], $configData);
        
        if ($testConnectivity) {
            $this->line('');
            $this->info("Testing connectivity for {$diskName}...");
            $this->testDiskConnectivity($diskName);
        }
    }

    /**
     * Get disk status
     */
    private function getDiskStatus(string $diskName, bool $test): string
    {
        if (!$test) {
            return 'not tested';
        }
        
        try {
            $disk = Storage::disk($diskName);
            
            // Try to perform a simple operation
            $testFile = 'test_' . time() . '.txt';
            $disk->put($testFile, 'test content');
            
            if ($disk->exists($testFile)) {
                $disk->delete($testFile);
                return '<fg=green>✓ OK</>';
            }
            
            return '<fg=red>✗ Failed</>';
        } catch (\Exception $e) {
            return '<fg=red>✗ Error</>';
        }
    }

    /**
     * Test storage connectivity
     */
    private function testStorageConnectivity(?string $specificDisk = null): void
    {
        $disksToTest = $specificDisk ? [$specificDisk] : array_keys(config('filesystems.disks'));
        
        foreach ($disksToTest as $diskName) {
            $this->testDiskConnectivity($diskName);
        }
    }

    /**
     * Test connectivity for a specific disk
     */
    private function testDiskConnectivity(string $diskName): void
    {
        try {
            $disk = Storage::disk($diskName);
            $testFile = 'connectivity_test_' . time() . '.txt';
            $testContent = 'This is a connectivity test file created at ' . now()->toDateTimeString();
            
            $this->line("Testing {$diskName}...");
            
            // Test write
            $this->line('  - Testing write operation...');
            $disk->put($testFile, $testContent);
            $this->info('    ✓ Write successful');
            
            // Test read
            $this->line('  - Testing read operation...');
            $content = $disk->get($testFile);
            if ($content === $testContent) {
                $this->info('    ✓ Read successful');
            } else {
                $this->error('    ✗ Read failed - content mismatch');
            }
            
            // Test exists
            $this->line('  - Testing exists operation...');
            if ($disk->exists($testFile)) {
                $this->info('    ✓ Exists check successful');
            } else {
                $this->error('    ✗ Exists check failed');
            }
            
            // Test delete
            $this->line('  - Testing delete operation...');
            $disk->delete($testFile);
            if (!$disk->exists($testFile)) {
                $this->info('    ✓ Delete successful');
            } else {
                $this->error('    ✗ Delete failed');
            }
            
            $this->info("✓ {$diskName} connectivity test passed");
            
        } catch (\Exception $e) {
            $this->error("✗ {$diskName} connectivity test failed: " . $e->getMessage());
            
            // Try to clean up test file if it exists
            try {
                if (isset($testFile) && Storage::disk($diskName)->exists($testFile)) {
                    Storage::disk($diskName)->delete($testFile);
                }
            } catch (\Exception $cleanupException) {
                // Ignore cleanup errors
            }
        }
        
        $this->line('');
    }
}