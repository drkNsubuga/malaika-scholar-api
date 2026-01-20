<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Http\Requests\ReviewApplicationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewApplicationController extends Controller
{
    /**
     * Review an application (approve/reject)
     */
    public function __invoke(ReviewApplicationRequest $request, Application $application): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Validate that application can be reviewed
            if (!in_array($application->status, ['pending', 'under_review'])) {
                return response()->json([
                    'error' => 'Application can only be reviewed when pending or under review',
                    'current_status' => $application->status
                ], 422);
            }

            $validated = $request->validated();

            // Update application with review data
            $application->update([
                'status' => $validated['status'],
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
                'review_notes' => $validated['review_notes'] ?? null,
                'score' => $validated['score'] ?? null,
            ]);

            // Create status history record
            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'status' => $validated['status'],
                'changed_by' => auth()->id(),
                'notes' => $validated['review_notes'] ?? 'Application reviewed',
                'changed_at' => now(),
            ]);

            // Log the review
            Log::info('Application reviewed', [
                'application_id' => $application->id,
                'reviewer_id' => auth()->id(),
                'new_status' => $validated['status'],
                'score' => $validated['score'] ?? null,
            ]);

            DB::commit();

            // Load fresh application with relationships
            $application->load(['user', 'opportunity', 'reviewedBy', 'statusHistory']);

            return response()->json([
                'message' => 'Application reviewed successfully',
                'application' => $application,
                'status' => $application->status,
                'reviewed_at' => $application->reviewed_at,
                'reviewer' => $application->reviewedBy->name ?? null,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Application review failed', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
                'reviewer_id' => auth()->id(),
            ]);

            return response()->json([
                'error' => 'Failed to review application',
                'message' => 'An error occurred while processing the review'
            ], 500);
        }
    }
}