<?php

namespace App\Console\Commands;

use App\Services\StorageService;
use Illuminate\Console\Command;

class CleanupTemporaryFiles extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'storage:cleanup-temp 
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--hours=24 : Files older than this many hours will be deleted}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up expired temporary files from storage';

    /**
     * Execute the console command.
     */
    public function handle(StorageService $storageService): int
    {
        $dryRun = $this->option('dry-run');
        $hours = (int) $this->option('hours');
        
        $this->info("Cleaning up temporary files older than {$hours} hours...");
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No files will actually be deleted');
        }
        
        try {
            if ($dryRun) {
                // In dry run mode, just show what would be deleted
                $this->showTemporaryFiles($hours);
                return Command::SUCCESS;
            }
            
            $deletedCount = $storageService->cleanupTemporaryFiles();
            
            if ($deletedCount > 0) {
                $this->info("Successfully deleted {$deletedCount} temporary files.");
            } else {
                $this->info('No temporary files found to delete.');
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to cleanup temporary files: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Show temporary files that would be deleted (dry run mode)
     */
    private function showTemporaryFiles(int $hours): void
    {
        try {
            $tempDisk = \Storage::disk('temp');
            $files = $tempDisk->allFiles();
            $expiredFiles = [];
            
            foreach ($files as $file) {
                $lastModified = $tempDisk->lastModified($file);
                
                if ($lastModified < now()->subHours($hours)->timestamp) {
                    $expiredFiles[] = [
                        'file' => $file,
                        'size' => $tempDisk->size($file),
                        'modified' => date('Y-m-d H:i:s', $lastModified)
                    ];
                }
            }
            
            if (empty($expiredFiles)) {
                $this->info('No expired temporary files found.');
                return;
            }
            
            $this->table(
                ['File', 'Size (bytes)', 'Last Modified'],
                array_map(function ($file) {
                    return [$file['file'], $file['size'], $file['modified']];
                }, $expiredFiles)
            );
            
            $totalSize = array_sum(array_column($expiredFiles, 'size'));
            $this->info("Total: " . count($expiredFiles) . " files, " . number_format($totalSize) . " bytes");
        } catch (\Exception $e) {
            $this->error('Failed to list temporary files: ' . $e->getMessage());
        }
    }
}