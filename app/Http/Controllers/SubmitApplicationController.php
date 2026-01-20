<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Http\Requests\SubmitApplicationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubmitApplicationController extends Controller
{
    /**
     * Submit an application for review
     */
    public function __invoke(SubmitApplicationRequest $request, Application $application): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Validate that application can be submitted
            if ($application->status !== 'draft') {
                return response()->json([
                    'error' => 'Application can only be submitted from draft status',
                    'current_status' => $application->status
                ], 422);
            }

            // Update application status
            $application->update([
                'status' => 'pending',
                'submitted_at' => now(),
            ]);

            // Create status history record
            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'status' => 'pending',
                'changed_by' => auth()->id(),
                'notes' => 'Application submitted for review',
                'changed_at' => now(),
            ]);

            // Log the submission
            Log::info('Application submitted', [
                'application_id' => $application->id,
                'user_id' => auth()->id(),
                'opportunity_id' => $application->opportunity_id,
            ]);

            DB::commit();

            // Load fresh application with relationships
            $application->load(['user', 'opportunity', 'statusHistory']);

            return response()->json([
                'message' => 'Application submitted successfully',
                'application' => $application,
                'status' => 'pending',
                'submitted_at' => $application->submitted_at,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Application submission failed', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'error' => 'Failed to submit application',
                'message' => 'An error occurred while processing your submission'
            ], 500);
        }
    }
}