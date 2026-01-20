<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PesapalPaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes (public)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/auth/resend-verification', [AuthController::class, 'resendVerification']);

    // File upload routes
    Route::prefix('files')->middleware('upload.rate.limit')->group(function () {
        Route::post('/upload', [FileUploadController::class, 'uploadDocument']);
        Route::post('/upload-temporary', [FileUploadController::class, 'uploadTemporary']);
        Route::post('/convert-temporary', [FileUploadController::class, 'convertTemporaryToDocument']);
    });

    // Document management routes
    Route::prefix('documents')->group(function () {
        Route::get('/{document}/download', [DocumentController::class, 'download']);
        Route::get('/{document}/signed-url', [DocumentController::class, 'getSignedUrl']);
        Route::get('/{document}/preview', [DocumentController::class, 'preview']);
        Route::delete('/{document}', [DocumentController::class, 'destroy']);
        Route::patch('/{document}/verify', [DocumentController::class, 'verify']);
    });

    // Secure document access (signed URLs)
    Route::get('/secure-access/{token}/{document}', [DocumentController::class, 'secureAccess'])
        ->name('documents.secure-access')
        ->middleware('signed');

    // Payment routes
    Route::prefix('payments')->group(function () {
        Route::post('/initiate', [PesapalPaymentController::class, 'initiatePayment']);
        Route::get('/{payment}/status', [PesapalPaymentController::class, 'checkStatus']);
        Route::post('/{payment}/refund', [PesapalPaymentController::class, 'processRefund']);
        Route::get('/history', [PesapalPaymentController::class, 'getPaymentHistory']);
    });

    // Admin only routes
    Route::middleware('role:Admin')->group(function () {
        Route::get('/admin/users', function () {
            return response()->json(['message' => 'Admin users endpoint']);
        });
        Route::get('/admin/system-stats', function () {
            return response()->json(['message' => 'System statistics']);
        });
    });

    // School routes
    Route::middleware('role:School,Admin')->group(function () {
        Route::get('/school/opportunities', function () {
            return response()->json(['message' => 'School opportunities endpoint']);
        });
        Route::get('/school/applications', function () {
            return response()->json(['message' => 'School applications endpoint']);
        });
    });

    // Student/Parent routes
    Route::middleware('role:Student/Parent,Admin')->group(function () {
        Route::get('/student/applications', function () {
            return response()->json(['message' => 'Student applications endpoint']);
        });
        Route::get('/student/opportunities', function () {
            return response()->json(['message' => 'Available opportunities']);
        });
    });

    // Sponsor routes
    Route::middleware('role:Sponsor,Admin')->group(function () {
        Route::get('/sponsor/students', function () {
            return response()->json(['message' => 'Sponsor students endpoint']);
        });
        Route::get('/sponsor/impact', function () {
            return response()->json(['message' => 'Sponsor impact metrics']);
        });
    });

    // Donor routes
    Route::middleware('role:Donor,Sponsor,Admin')->group(function () {
        Route::get('/donor/materials', function () {
            return response()->json(['message' => 'Donor materials endpoint']);
        });
        Route::post('/donor/donate', function () {
            return response()->json(['message' => 'Donation endpoint']);
        });
    });
});

// Public payment callback routes (no authentication required)
Route::prefix('payments/pesapal')->group(function () {
    Route::get('/callback', [PesapalPaymentController::class, 'callback']);
    Route::post('/ipn', [PesapalPaymentController::class, 'ipn']);
});