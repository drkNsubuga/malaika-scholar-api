<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\StorageService;
use App\Services\SecureUrlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function __construct(
        private StorageService $storageService,
        private SecureUrlService $secureUrlService
    ) {
        $this->middleware('auth:sanctum')->except(['secureAccess']);
    }

    /**
     * Download a document with access control
     */
    public function download(Document $document): StreamedResponse|JsonResponse
    {
        try {
            // Check access permissions
            if (!$this->canAccessDocument($document)) {
                return response()->json([
                    'error' => 'Access denied'
                ], 403);
            }

            // Check if file exists
            if (!$document->existsInStorage()) {
                return response()->json([
                    'error' => 'File not found'
                ], 404);
            }

            // Get file from storage
            $disk = Storage::disk('documents');
            $filePath = $document->file_path;

            // Log access for audit trail
            Log::info('Document accessed', [
                'document_id' => $document->id,
                'user_id' => Auth::id(),
                'file_name' => $document->original_name,
                'ip_address' => request()->ip()
            ]);

            // Stream file download
            return $disk->download($filePath, $document->original_name, [
                'Content-Type' => $document->mime_type,
                'Content-Security-Policy' => config('security.csp.file_download'),
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'DENY',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (\Exception $e) {
            Log::error('Document download failed', [
                'document_id' => $document->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Download failed'
            ], 500);
        }
    }

    /**
     * Get a signed URL for secure document access
     */
    public function getSignedUrl(Document $document): JsonResponse
    {
        try {
            // Check access permissions
            if (!$this->canAccessDocument($document)) {
                return response()->json([
                    'error' => 'Access denied'
                ], 403);
            }

            // Check if file exists
            if (!$document->existsInStorage()) {
                return response()->json([
                    'error' => 'File not found'
                ], 404);
            }

            // Generate secure URL using SecureUrlService
            $secureUrl = $this->secureUrlService->generateSecureUrl($document, 60);
            
            if (!$secureUrl) {
                return response()->json([
                    'error' => 'Unable to generate access URL'
                ], 500);
            }

            // Log URL generation for audit trail
            Log::info('Document URL generated', [
                'document_id' => $document->id,
                'user_id' => Auth::id(),
                'expires_at' => now()->addHour()
            ]);

            return response()->json([
                'url' => $secureUrl,
                'expires_at' => now()->addHour()->toISOString(),
                'document' => [
                    'id' => $document->id,
                    'name' => $document->original_name,
                    'size' => $document->file_size_formatted,
                    'type' => $document->mime_type
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Signed URL generation failed', [
                'document_id' => $document->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'URL generation failed'
            ], 500);
        }
    }

    /**
     * Preview document (for images and PDFs)
     */
    public function preview(Document $document): StreamedResponse|JsonResponse
    {
        try {
            // Check access permissions
            if (!$this->canAccessDocument($document)) {
                return response()->json([
                    'error' => 'Access denied'
                ], 403);
            }

            // Check if file type supports preview
            $previewableMimeTypes = [
                'image/jpeg',
                'image/png',
                'image/gif',
                'application/pdf'
            ];

            if (!in_array($document->mime_type, $previewableMimeTypes)) {
                return response()->json([
                    'error' => 'File type not previewable'
                ], 400);
            }

            // Check if file exists
            if (!$document->existsInStorage()) {
                return response()->json([
                    'error' => 'File not found'
                ], 404);
            }

            // Get file from storage
            $disk = Storage::disk('documents');
            $filePath = $document->file_path;

            // Log preview access
            Log::info('Document previewed', [
                'document_id' => $document->id,
                'user_id' => Auth::id(),
                'file_name' => $document->original_name
            ]);

            // Stream file for preview
            return $disk->response($filePath, null, [
                'Content-Type' => $document->mime_type,
                'Content-Security-Policy' => config('security.csp.image_display'),
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'SAMEORIGIN',
                'Cache-Control' => 'private, max-age=3600'
            ]);

        } catch (\Exception $e) {
            Log::error('Document preview failed', [
                'document_id' => $document->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Preview failed'
            ], 500);
        }
    }

    /**
     * Delete a document
     */
    public function destroy(Document $document): JsonResponse
    {
        try {
            // Check access permissions
            if (!$this->canModifyDocument($document)) {
                return response()->json([
                    'error' => 'Access denied'
                ], 403);
            }

            // Log deletion for audit trail
            Log::info('Document deletion initiated', [
                'document_id' => $document->id,
                'user_id' => Auth::id(),
                'file_name' => $document->original_name
            ]);

            // Delete file from storage (handled by model boot method)
            $document->delete();

            return response()->json([
                'message' => 'Document deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Document deletion failed', [
                'document_id' => $document->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Deletion failed'
            ], 500);
        }
    }

    /**
     * Verify a document (admin/reviewer only)
     */
    public function verify(Document $document, Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:verified,rejected',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            // Check if user can verify documents
            if (!$this->canVerifyDocument($document)) {
                return response()->json([
                    'error' => 'Access denied'
                ], 403);
            }

            $status = $request->input('status');
            $notes = $request->input('notes');

            // Update document verification status
            $document->update([
                'status' => $status,
                'verification_notes' => $notes,
                'verified_at' => now(),
                'verified_by' => Auth::id()
            ]);

            // Log verification action
            Log::info('Document verification updated', [
                'document_id' => $document->id,
                'verified_by' => Auth::id(),
                'status' => $status,
                'notes' => $notes
            ]);

            return response()->json([
                'message' => 'Document verification updated',
                'document' => $document->fresh()->load(['documentType', 'verifiedBy'])
            ]);

        } catch (\Exception $e) {
            Log::error('Document verification failed', [
                'document_id' => $document->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Verification failed'
            ], 500);
        }
    }

    /**
     * Check if user can access document
     */
    private function canAccessDocument(Document $document): bool
    {
        $user = Auth::user();

        // Admin can access all documents
        if ($user->role === 'Admin') {
            return true;
        }

        // Owner can access their own documents
        if ($document->user_id === $user->id) {
            return true;
        }

        // Public documents can be accessed by anyone
        if ($document->is_public) {
            return true;
        }

        // Check if user has access through documentable relationship
        return $this->hasDocumentableAccess($document, $user);
    }

    /**
     * Check if user can modify document
     */
    private function canModifyDocument(Document $document): bool
    {
        $user = Auth::user();

        // Admin can modify all documents
        if ($user->role === 'Admin') {
            return true;
        }

        // Owner can modify their own documents
        return $document->user_id === $user->id;
    }

    /**
     * Secure document access via signed URL
     */
    public function secureAccess(string $token, Document $document): StreamedResponse|JsonResponse
    {
        try {
            // Validate the secure token
            $validation = $this->secureUrlService->validateSecureToken($token, $document->id);
            
            if (!$validation['valid']) {
                return response()->json([
                    'error' => $validation['error']
                ], 403);
            }

            // Check if file exists
            if (!$document->existsInStorage()) {
                return response()->json([
                    'error' => 'File not found'
                ], 404);
            }

            // Get file from storage
            $disk = Storage::disk('documents');
            $filePath = $document->file_path;

            // Log secure access for audit trail
            Log::info('Secure document access', [
                'document_id' => $document->id,
                'user_id' => $validation['user_id'],
                'token' => substr($token, 0, 8) . '...',
                'ip_address' => request()->ip()
            ]);

            // Stream file download
            return $disk->download($filePath, $document->original_name, [
                'Content-Type' => $document->mime_type,
                'Content-Security-Policy' => config('security.csp.file_download'),
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'DENY',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (\Exception $e) {
            Log::error('Secure document access failed', [
                'document_id' => $document->id,
                'token' => substr($token, 0, 8) . '...',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Access failed'
            ], 500);
        }
    }

    /**
     * Check if user can verify documents
     */
    private function canVerifyDocument(Document $document): bool
    {
        $user = Auth::user();

        // Only admins and schools can verify documents
        return in_array($user->role, ['Admin', 'School']);
    }

    /**
     * Check access through documentable relationship
     */
    private function hasDocumentableAccess(Document $document, $user): bool
    {
        $documentable = $document->documentable;

        if (!$documentable) {
            return false;
        }

        // Check access based on documentable type
        switch ($document->documentable_type) {
            case 'App\Models\Application':
                // Schools can access documents for applications to their opportunities
                if ($user->role === 'School') {
                    return $documentable->opportunity && 
                           $documentable->opportunity->sponsor_id === $user->id;
                }
                break;

            case 'App\Models\Opportunity':
                // Schools can access documents for their opportunities
                if ($user->role === 'School') {
                    return $documentable->sponsor_id === $user->id;
                }
                break;
        }

        return false;
    }
}