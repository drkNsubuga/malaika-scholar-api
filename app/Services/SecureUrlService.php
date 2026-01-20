<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SecureUrlService
{
    /**
     * Generate a secure, time-limited URL for document access
     */
    public function generateSecureUrl(Document $document, int $expirationMinutes = 60): string
    {
        $token = $this->generateSecureToken($document);
        $expiresAt = now()->addMinutes($expirationMinutes);
        
        // Store token in cache with expiration
        Cache::put(
            "secure_url_token:{$token}",
            [
                'document_id' => $document->id,
                'user_id' => auth()->id(),
                'expires_at' => $expiresAt,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ],
            $expirationMinutes * 60 // Cache TTL in seconds
        );

        // Generate signed URL
        return URL::temporarySignedRoute(
            'documents.secure-access',
            $expiresAt,
            [
                'token' => $token,
                'document' => $document->id
            ]
        );
    }

    /**
     * Validate and consume a secure URL token
     */
    public function validateSecureToken(string $token, int $documentId): array
    {
        $cacheKey = "secure_url_token:{$token}";
        $tokenData = Cache::get($cacheKey);

        if (!$tokenData) {
            return [
                'valid' => false,
                'error' => 'Token not found or expired'
            ];
        }

        // Validate document ID matches
        if ($tokenData['document_id'] !== $documentId) {
            return [
                'valid' => false,
                'error' => 'Token document mismatch'
            ];
        }

        // Check expiration
        if (Carbon::parse($tokenData['expires_at'])->isPast()) {
            Cache::forget($cacheKey);
            return [
                'valid' => false,
                'error' => 'Token expired'
            ];
        }

        // Optional: Validate IP address (for enhanced security)
        if (config('security.file_upload.validate_ip_on_access', false)) {
            if ($tokenData['ip_address'] !== request()->ip()) {
                return [
                    'valid' => false,
                    'error' => 'IP address mismatch'
                ];
            }
        }

        // Token is valid - consume it (one-time use)
        Cache::forget($cacheKey);

        return [
            'valid' => true,
            'user_id' => $tokenData['user_id'],
            'document_id' => $tokenData['document_id']
        ];
    }

    /**
     * Generate a cryptographically secure token
     */
    private function generateSecureToken(Document $document): string
    {
        $payload = [
            'document_id' => $document->id,
            'user_id' => auth()->id(),
            'timestamp' => now()->timestamp,
            'random' => Str::random(32)
        ];

        return hash('sha256', json_encode($payload) . config('app.key'));
    }

    /**
     * Generate a temporary upload URL for direct-to-storage uploads
     */
    public function generateUploadUrl(string $filename, string $contentType, int $expirationMinutes = 15): array
    {
        $key = 'temp-uploads/' . Str::uuid() . '/' . $filename;
        $disk = \Storage::disk('documents');

        // For S3, generate presigned POST URL
        if ($disk->getConfig()['driver'] === 's3') {
            $s3Client = $disk->getAdapter()->getClient();
            $bucket = $disk->getConfig()['bucket'];

            $postObject = $s3Client->createPresignedRequest(
                $s3Client->getCommand('PutObject', [
                    'Bucket' => $bucket,
                    'Key' => $key,
                    'ContentType' => $contentType,
                    'ContentLength' => config('security.file_upload.max_file_size', 10485760)
                ]),
                "+{$expirationMinutes} minutes"
            );

            return [
                'upload_url' => (string) $postObject->getUri(),
                'method' => 'PUT',
                'headers' => [
                    'Content-Type' => $contentType
                ],
                'key' => $key,
                'expires_at' => now()->addMinutes($expirationMinutes)
            ];
        }

        // For local storage, return regular upload endpoint
        return [
            'upload_url' => route('files.upload-temporary'),
            'method' => 'POST',
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . auth()->user()->currentAccessToken()->plainTextToken
            ],
            'key' => $key,
            'expires_at' => now()->addMinutes($expirationMinutes)
        ];
    }

    /**
     * Revoke all active tokens for a document
     */
    public function revokeDocumentTokens(Document $document): int
    {
        $pattern = "secure_url_token:*";
        $keys = Cache::getRedis()->keys($pattern);
        $revokedCount = 0;

        foreach ($keys as $key) {
            $tokenData = Cache::get($key);
            if ($tokenData && $tokenData['document_id'] === $document->id) {
                Cache::forget($key);
                $revokedCount++;
            }
        }

        return $revokedCount;
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens(): int
    {
        $pattern = "secure_url_token:*";
        $keys = Cache::getRedis()->keys($pattern);
        $cleanedCount = 0;

        foreach ($keys as $key) {
            $tokenData = Cache::get($key);
            if ($tokenData && Carbon::parse($tokenData['expires_at'])->isPast()) {
                Cache::forget($key);
                $cleanedCount++;
            }
        }

        return $cleanedCount;
    }
}