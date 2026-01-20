<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (
            auth()->user()->hasRole('student') || 
            auth()->user()->hasRole('parent') ||
            auth()->user()->hasRole('admin')
        );
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $step = $this->input('step', 'basic');
        
        return match($step) {
            'basic' => $this->basicStepRules(),
            'student_profile' => $this->studentProfileStepRules(),
            'academic_info' => $this->academicInfoStepRules(),
            'financial_info' => $this->financialInfoStepRules(),
            'documents' => $this->documentsStepRules(),
            default => $this->basicStepRules()
        };
    }

    /**
     * Basic application information validation
     */
    private function basicStepRules(): array
    {
        return [
            'opportunity_id' => [
                'required',
                'exists:opportunities,id'
            ],
            'guardian_id' => [
                'nullable',
                'exists:users,id'
            ],
            'step' => [
                'required',
                'string',
                Rule::in(['basic', 'student_profile', 'academic_info', 'financial_info', 'documents'])
            ]
        ];
    }

    /**
     * Student profile step validation
     */
    private function studentProfileStepRules(): array
    {
        return [
            'student_profile_id' => [
                'required',
                'exists:student_profiles,id'
            ],
            'step' => [
                'required',
                'string',
                'in:student_profile'
            ]
        ];
    }

    /**
     * Academic information step validation
     */
    private function academicInfoStepRules(): array
    {
        return [
            'academic_data' => [
                'required',
                'array'
            ],
            'academic_data.current_grade' => [
                'required',
                'string',
                'max:50'
            ],
            'academic_data.gpa' => [
                'nullable',
                'numeric',
                'min:0',
                'max:4'
            ],
            'academic_data.achievements' => [
                'nullable',
                'array'
            ],
            'academic_data.extracurricular' => [
                'nullable',
                'array'
            ],
            'step' => [
                'required',
                'string',
                'in:academic_info'
            ]
        ];
    }

    /**
     * Financial information step validation
     */
    private function financialInfoStepRules(): array
    {
        return [
            'financial_data' => [
                'required',
                'array'
            ],
            'financial_data.household_income' => [
                'required',
                'numeric',
                'min:0'
            ],
            'financial_data.family_size' => [
                'required',
                'integer',
                'min:1',
                'max:20'
            ],
            'financial_data.financial_need' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'step' => [
                'required',
                'string',
                'in:financial_info'
            ]
        ];
    }

    /**
     * Documents step validation
     */
    private function documentsStepRules(): array
    {
        return [
            'documents' => [
                'nullable',
                'array'
            ],
            'documents.*.document_type_id' => [
                'required',
                'exists:document_types,id'
            ],
            'documents.*.file_path' => [
                'required',
                'string'
            ],
            'documents.*.original_name' => [
                'required',
                'string',
                'max:255'
            ],
            'step' => [
                'required',
                'string',
                'in:documents'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'opportunity_id.required' => 'Please select an opportunity to apply for.',
            'opportunity_id.exists' => 'The selected opportunity does not exist.',
            'student_profile_id.required' => 'Student profile is required.',
            'student_profile_id.exists' => 'The selected student profile does not exist.',
            'academic_data.required' => 'Academic information is required.',
            'academic_data.current_grade.required' => 'Current grade level is required.',
            'academic_data.gpa.numeric' => 'GPA must be a valid number.',
            'academic_data.gpa.max' => 'GPA cannot exceed 4.0.',
            'financial_data.required' => 'Financial information is required.',
            'financial_data.household_income.required' => 'Household income is required.',
            'financial_data.household_income.numeric' => 'Household income must be a valid number.',
            'financial_data.family_size.required' => 'Family size is required.',
            'financial_data.family_size.integer' => 'Family size must be a whole number.',
            'financial_data.family_size.min' => 'Family size must be at least 1.',
            'financial_data.financial_need.max' => 'Financial need description cannot exceed 1000 characters.',
            'documents.*.document_type_id.required' => 'Document type is required for each uploaded file.',
            'documents.*.document_type_id.exists' => 'Invalid document type selected.',
            'documents.*.file_path.required' => 'File path is required for each document.',
            'documents.*.original_name.required' => 'Original file name is required.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate opportunity is still accepting applications
            if ($this->has('opportunity_id')) {
                $opportunity = \App\Models\Opportunity::find($this->input('opportunity_id'));
                
                if ($opportunity) {
                    if ($opportunity->status !== 'active') {
                        $validator->errors()->add('opportunity_id', 'This opportunity is no longer accepting applications.');
                    }
                    
                    if ($opportunity->deadline && $opportunity->deadline < now()) {
                        $validator->errors()->add('opportunity_id', 'The application deadline for this opportunity has passed.');
                    }
                }
            }

            // Validate guardian relationship if provided
            if ($this->has('guardian_id') && $this->input('guardian_id')) {
                $guardian = \App\Models\User::find($this->input('guardian_id'));
                
                if ($guardian && !$guardian->hasRole('parent')) {
                    $validator->errors()->add('guardian_id', 'The selected guardian must have a parent role.');
                }
            }
        });
    }
}