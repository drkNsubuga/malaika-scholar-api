<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $application = $this->route('application');
        
        // User can review applications for opportunities they created or be an admin
        return $application && (
            (auth()->user()->hasRole('school') && 
             $application->opportunity->created_by === auth()->id()) ||
            auth()->user()->hasRole('admin')
        );
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(['approved', 'rejected', 'under_review', 'waitlisted'])
            ],
            'review_notes' => [
                'nullable',
                'string',
                'max:2000'
            ],
            'score' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Review status is required.',
            'status.in' => 'Invalid review status. Must be one of: approved, rejected, under_review, waitlisted.',
            'review_notes.max' => 'Review notes cannot exceed 2000 characters.',
            'score.numeric' => 'Score must be a valid number.',
            'score.min' => 'Score cannot be less than 0.',
            'score.max' => 'Score cannot be greater than 100.',
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

            // Validate application can be reviewed
            if (!in_array($application->status, ['pending', 'under_review'])) {
                $validator->errors()->add('status', 'Application can only be reviewed when pending or under review.');
            }

            // Validate score is provided for approved applications
            if ($this->input('status') === 'approved' && !$this->has('score')) {
                $validator->errors()->add('score', 'Score is required when approving an application.');
            }

            // Validate review notes for rejected applications
            if ($this->input('status') === 'rejected' && !$this->filled('review_notes')) {
                $validator->errors()->add('review_notes', 'Review notes are required when rejecting an application.');
            }
        });
    }
}