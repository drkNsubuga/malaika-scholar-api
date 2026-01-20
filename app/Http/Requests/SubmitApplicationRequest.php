<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $application = $this->route('application');
        
        // User can submit their own application or applications they created as guardian
        return $application && (
            $application->user_id === auth()->id() || 
            $application->guardian_id === auth()->id() ||
            auth()->user()->hasRole('admin')
        );
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // No additional fields required for submission
            // The controller will validate the application state
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Custom messages if needed
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $application = $this->route('application');
            
            if (!$application) {
                $validator->errors()->add('application', 'Application not found.');
                return;
            }

            // Validate application completeness before submission
            $this->validateApplicationCompleteness($validator, $application);
        });
    }

    /**
     * Validate that the application is complete and ready for submission
     */
    private function validateApplicationCompleteness($validator, $application): void
    {
        // Check if required student profile exists
        if (!$application->student_profile_id || !$application->studentProfile) {
            $validator->errors()->add('student_profile', 'Student profile is required before submission.');
        }

        // Check if application has required data
        $requiredDataCount = $application->applicationData()
            ->whereIn('field_name', ['personal_info', 'academic_info', 'financial_info'])
            ->count();
            
        if ($requiredDataCount < 3) {
            $validator->errors()->add('application_data', 'All required application sections must be completed.');
        }

        // Check if required documents are uploaded
        $requiredDocuments = $application->opportunity->application_requirements ?? [];
        if (!empty($requiredDocuments)) {
            $uploadedDocumentTypes = $application->documents()
                ->pluck('document_type_id')
                ->toArray();
                
            foreach ($requiredDocuments as $requiredDoc) {
                if (isset($requiredDoc['required']) && $requiredDoc['required'] && 
                    !in_array($requiredDoc['document_type_id'], $uploadedDocumentTypes)) {
                    $validator->errors()->add('documents', "Required document '{$requiredDoc['name']}' is missing.");
                }
            }
        }

        // Validate application status
        if ($application->status !== 'draft') {
            $validator->errors()->add('status', 'Only draft applications can be submitted.');
        }

        // Check opportunity deadline
        if ($application->opportunity->deadline && $application->opportunity->deadline < now()) {
            $validator->errors()->add('deadline', 'Application deadline has passed.');
        }

        // Check opportunity status
        if ($application->opportunity->status !== 'active') {
            $validator->errors()->add('opportunity', 'This opportunity is no longer accepting applications.');
        }
    }
}