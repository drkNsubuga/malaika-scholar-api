<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FileUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB max file size
                'mimes:pdf,jpg,jpeg,png,gif,doc,docx,txt'
            ],
            'document_type_id' => [
                'required',
                'exists:document_types,id'
            ],
            'documentable_type' => [
                'required',
                'string',
                Rule::in(['App\Models\Application', 'App\Models\Opportunity', 'App\Models\User'])
            ],
            'documentable_id' => [
                'required',
                'integer',
                'min:1'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to upload.',
            'file.file' => 'The uploaded file is not valid.',
            'file.max' => 'The file size must not exceed 10MB.',
            'file.mimes' => 'The file must be a PDF, image (JPG, PNG, GIF), or document (DOC, DOCX, TXT).',
            'document_type_id.required' => 'Please specify the document type.',
            'document_type_id.exists' => 'The selected document type is invalid.',
            'documentable_type.required' => 'The document context type is required.',
            'documentable_type.in' => 'The document context type is invalid.',
            'documentable_id.required' => 'The document context ID is required.',
            'documentable_id.integer' => 'The document context ID must be a valid number.',
            'documentable_id.min' => 'The document context ID must be greater than 0.'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation logic can be added here
            $file = $this->file('file');
            
            if ($file) {
                // Check for executable files (security measure)
                $dangerousExtensions = ['exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar', 'php', 'py', 'rb', 'sh'];
                $extension = strtolower($file->getClientOriginalExtension());
                
                if (in_array($extension, $dangerousExtensions)) {
                    $validator->errors()->add('file', 'Executable files are not allowed for security reasons.');
                }

                // Check file name for suspicious patterns
                $filename = $file->getClientOriginalName();
                if (preg_match('/[<>:"|?*]/', $filename)) {
                    $validator->errors()->add('file', 'File name contains invalid characters.');
                }

                // Check for double extensions (security measure)
                if (substr_count($filename, '.') > 1) {
                    $parts = explode('.', $filename);
                    if (count($parts) > 2) {
                        $validator->errors()->add('file', 'Files with multiple extensions are not allowed.');
                    }
                }
            }
        });
    }
}