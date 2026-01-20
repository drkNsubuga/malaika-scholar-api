<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
use App\Services\StorageService;
use App\Services\VirusScanService;
use App\Models\Document;
use App\Models\DocumentType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FileUploadController extends Controller
{
    public function __construct(
        private StorageService $storageService,
        private VirusScanService $virusScanService
    ) {
        $this->middleware('auth:sanctum');
    }

    /**
     * Upload a document file
     */
    public function uploadDocument(FileUploadRequest $request): JsonResponse
    {
        try {
            $file = $request->file('file');
            $documentTypeId = $request->input('document_type_id');
            $documentableType = $request->input('documentable_type');
            $documentableId = $request->input('documentable_id');

            // Validate document type
            $documentType = DocumentType::find($documentTypeId);
            if (!$documentType) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid document type'
                ], 400);
            }

            // Validate file format against document type
            $fileExtension = strtolower($file->getClientOriginalExtension());
            if (!in_array($fileExtension, $documentType->allowed_formats)) {
                return response()->json([
                    'success' => false,
                    'error' => "File format '{$fileExtension}' not allowed for this document type. Allowed formats: " . implode(', ', $documentType->allowed_formats)
                ], 400);
            }

            // Validate file size against document type
            if ($documentType->max_file_size && $file->getSize() > $documentType->max_file_size) {
                return response()->json([
                    'success' => false,
                    'error' => "File size exceeds maximum allowed size of " . $this->formatBytes($documentType->max_file_size)
                ], 400);
            }

            // Perform virus scan
            $scanResult = $this->virusScanService->scanFile($file);
            if (!$scanResult['clean']) {
                Log::warning('Virus detected in uploaded file', [
                    'user_id' => Auth::id(),
                    'filename' => $file->getClientOriginalName(),
                    'scan_result' => $scanResult
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'File failed security scan'
                ], 400);
            }

            // Store file using StorageService
            $storageResult = $this->storageService->storeDocument($file, 'documents');
            
            if (!$storageResult['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $storageResult['error']
                ], 500);
            }

            // Create document record
            $document = Document::create([
                'user_id' => Auth::id(),
                'document_type_id' => $documentTypeId,
                'documentable_type' => $documentableType,
                'documentable_id' => $documentableId,
                'original_name' => $storageResult['original_name'],
                'file_name' => $storageResult['filename'],
                'file_path' => $storageResult['path'],
                'mime_type' => $storageResult['mime_type'],
                'file_size' => $storageResult['size'],
                'status' => 'uploaded',
                'is_public' => false
            ]);

            return response()->json([
                'success' => true,
                'document' => $document->load('documentType'),
                'url' => $storageResult['url']
            ], 201);

        } catch (\Exception $e) {
            Log::error('Document upload failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Upload failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Upload temporary file (for multi-step processes)
     */
    public function uploadTemporary(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max for temp files
        ]);

        try {
            $file = $request->file('file');

            // Basic security validation
            $this->validateFileType($file);

            // Perform virus scan
            $scanResult = $this->virusScanService->scanFile($file);
            if (!$scanResult['clean']) {
                return response()->json([
                    'success' => false,
                    'error' => 'File failed security scan'
                ], 400);
            }

            // Store as temporary file
            $storageResult = $this->storageService->storeTemporary($file);
            
            if (!$storageResult['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $storageResult['error']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'temp_path' => $storageResult['path'],
                'filename' => $storageResult['filename'],
                'original_name' => $storageResult['original_name'],
                'size' => $storageResult['size'],
                'expires_at' => $storageResult['expires_at']
            ], 201);

        } catch (\Exception $e) {
            Log::error('Temporary file upload failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Upload failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Convert temporary file to permanent document
     */
    public function convertTemporaryToDocument(Request $request): JsonResponse
    {
        $request->validate([
            'temp_path' => 'required|string',
            'document_type_id' => 'required|exists:document_types,id',
            'documentable_type' => 'required|string',
            'documentable_id' => 'required|integer'
        ]);

        try {
            $tempPath = $request->input('temp_path');
            $documentTypeId = $request->input('document_type_id');
            $documentableType = $request->input('documentable_type');
            $documentableId = $request->input('documentable_id');

            // Move file from temporary to permanent storage
            $moveResult = $this->storageService->moveFromTemporary($tempPath);
            
            if (!$moveResult['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $moveResult['error']
                ], 400);
            }

            // Get file info from temp storage before it was moved
            $originalName = basename($tempPath);
            
            // Create document record
            $document = Document::create([
                'user_id' => Auth::id(),
                'document_type_id' => $documentTypeId,
                'documentable_type' => $documentableType,
                'documentable_id' => $documentableId,
                'original_name' => $originalName,
                'file_name' => basename($moveResult['path']),
                'file_path' => $moveResult['path'],
                'mime_type' => mime_content_type($moveResult['path']) ?? 'application/octet-stream',
                'file_size' => $this->storageService->getFileSize($moveResult['path']),
                'status' => 'uploaded',
                'is_public' => false
            ]);

            return response()->json([
                'success' => true,
                'document' => $document->load('documentType'),
                'url' => $moveResult['url']
            ], 201);

        } catch (\Exception $e) {
            Log::error('Temporary to permanent conversion failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Conversion failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Validate file type for security
     */
    private function validateFileType($file): void
    {
        $allowedMimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'
        ];

        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'txt'];

        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($mimeType, $allowedMimeTypes) || !in_array($extension, $allowedExtensions)) {
            throw new \InvalidArgumentException('File type not allowed');
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}