<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class StorageService
{
    /**
     * Store a document file
     */
    public function storeDocument(UploadedFile $file, string $directory = 'documents'): array
    {
        try {
            $disk = Storage::disk('documents');
            $filename = $this->generateUniqueFilename($file);
            $path = $directory . '/' . $filename;
            
            // Store the file
            $storedPath = $disk->putFileAs($directory, $file, $filename);
            
            return [
                'success' => true,
                'path' => $storedPath,
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'disk' => 'documents',
                'url' => $this->getDocumentUrl($storedPath)
            ];
        } catch (\Exception $e) {
            Log::error('Document storage failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to store document: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Store an avatar/profile image
     */
    public function storeAvatar(UploadedFile $file, string $userId): array
    {
        try {
            $disk = Storage::disk('avatars');
            $filename = 'avatar_' . $userId . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $disk->putFileAs('', $file, $filename);
            
            return [
                'success' => true,
                'path' => $path,
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'disk' => 'avatars',
                'url' => $this->getAvatarUrl($path)
            ];
        } catch (\Exception $e) {
            Log::error('Avatar storage failed', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'file' => $file->getClientOriginalName()
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to store avatar: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Store a temporary file
     */
    public function storeTemporary(UploadedFile $file): array
    {
        try {
            $disk = Storage::disk('temp');
            $filename = $this->generateUniqueFilename($file);
            $path = $disk->putFileAs('', $file, $filename);
            
            return [
                'success' => true,
                'path' => $path,
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'disk' => 'temp',
                'expires_at' => now()->addHours(24) // Temp files expire in 24 hours
            ];
        } catch (\Exception $e) {
            Log::error('Temporary file storage failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to store temporary file: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get a secure URL for a document
     */
    public function getDocumentUrl(string $path): ?string
    {
        try {
            $disk = Storage::disk('documents');
            
            // For S3 and other cloud storage, generate signed URL
            if ($disk->getConfig()['driver'] === 's3') {
                return $disk->temporaryUrl($path, now()->addHours(1));
            }
            
            // For local storage, return app URL
            return $disk->url($path);
        } catch (\Exception $e) {
            Log::error('Failed to generate document URL', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get URL for avatar
     */
    public function getAvatarUrl(string $path): ?string
    {
        try {
            $disk = Storage::disk('avatars');
            return $disk->url($path);
        } catch (\Exception $e) {
            Log::error('Failed to generate avatar URL', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Delete a file from storage
     */
    public function deleteFile(string $path, string $disk = 'documents'): bool
    {
        try {
            return Storage::disk($disk)->delete($path);
        } catch (\Exception $e) {
            Log::error('File deletion failed', [
                'path' => $path,
                'disk' => $disk,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Move file from temporary to permanent storage
     */
    public function moveFromTemporary(string $tempPath, string $targetDirectory = 'documents'): array
    {
        try {
            $tempDisk = Storage::disk('temp');
            $targetDisk = Storage::disk('documents');
            
            if (!$tempDisk->exists($tempPath)) {
                return [
                    'success' => false,
                    'error' => 'Temporary file not found'
                ];
            }
            
            $filename = basename($tempPath);
            $targetPath = $targetDirectory . '/' . $filename;
            
            // Copy file content
            $content = $tempDisk->get($tempPath);
            $targetDisk->put($targetPath, $content);
            
            // Delete temporary file
            $tempDisk->delete($tempPath);
            
            return [
                'success' => true,
                'path' => $targetPath,
                'disk' => 'documents',
                'url' => $this->getDocumentUrl($targetPath)
            ];
        } catch (\Exception $e) {
            Log::error('File move from temporary failed', [
                'temp_path' => $tempPath,
                'target_directory' => $targetDirectory,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to move file: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if file exists
     */
    public function fileExists(string $path, string $disk = 'documents'): bool
    {
        try {
            return Storage::disk($disk)->exists($path);
        } catch (\Exception $e) {
            Log::error('File existence check failed', [
                'path' => $path,
                'disk' => $disk,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get file size
     */
    public function getFileSize(string $path, string $disk = 'documents'): ?int
    {
        try {
            return Storage::disk($disk)->size($path);
        } catch (\Exception $e) {
            Log::error('File size check failed', [
                'path' => $path,
                'disk' => $disk,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $basename = Str::slug($basename);
        
        return $basename . '_' . Str::random(8) . '_' . time() . '.' . $extension;
    }

    /**
     * Get storage configuration info
     */
    public function getStorageInfo(): array
    {
        return [
            'default_disk' => config('filesystems.default'),
            'documents_driver' => config('filesystems.disks.documents.driver'),
            'avatars_driver' => config('filesystems.disks.avatars.driver'),
            'backup_driver' => config('filesystems.disks.backups.driver'),
            'available_disks' => array_keys(config('filesystems.disks')),
        ];
    }

    /**
     * Clean up expired temporary files
     */
    public function cleanupTemporaryFiles(): int
    {
        try {
            $tempDisk = Storage::disk('temp');
            $files = $tempDisk->allFiles();
            $deletedCount = 0;
            
            foreach ($files as $file) {
                $lastModified = $tempDisk->lastModified($file);
                
                // Delete files older than 24 hours
                if ($lastModified < now()->subHours(24)->timestamp) {
                    if ($tempDisk->delete($file)) {
                        $deletedCount++;
                    }
                }
            }
            
            Log::info('Temporary files cleanup completed', [
                'deleted_count' => $deletedCount
            ]);
            
            return $deletedCount;
        } catch (\Exception $e) {
            Log::error('Temporary files cleanup failed', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
}