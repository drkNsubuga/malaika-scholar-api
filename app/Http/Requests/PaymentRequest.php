<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
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
        $supportedCurrencies = config('services.pesapal.supported_currencies', ['KES', 'USD']);
        
        return [
            'amount' => [
                'required',
                'numeric',
                'min:1',
                'max:1000000' // 1 million maximum
            ],
            'currency' => [
                'nullable',
                'string',
                'size:3',
                Rule::in($supportedCurrencies)
            ],
            'description' => [
                'required',
                'string',
                'max:255',
                'min:5'
            ],
            'payment_type' => [
                'required',
                'string',
                Rule::in([
                    'Scholarship Support',
                    'Material Donation', 
                    'General Donation',
                    'Application Fee',
                    'Promotion Fee'
                ])
            ],
            'payable_type' => [
                'nullable',
                'string',
                Rule::in([
                    'App\Models\Application',
                    'App\Models\Student',
                    'App\Models\Opportunity',
                    'App\Models\ScholasticMaterial'
                ])
            ],
            'payable_id' => [
                'nullable',
                'integer',
                'min:1'
            ],
            'recipient_id' => [
                'nullable',
                'exists:users,id'
            ],
            
            // Billing address fields
            'country_code' => [
                'nullable',
                'string',
                'size:2'
            ],
            'address_line_1' => [
                'nullable',
                'string',
                'max:100'
            ],
            'address_line_2' => [
                'nullable',
                'string',
                'max:100'
            ],
            'city' => [
                'nullable',
                'string',
                'max:50'
            ],
            'state' => [
                'nullable',
                'string',
                'max:50'
            ],
            'postal_code' => [
                'nullable',
                'string',
                'max:20'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Payment amount is required.',
            'amount.numeric' => 'Payment amount must be a valid number.',
            'amount.min' => 'Payment amount must be at least 1.',
            'amount.max' => 'Payment amount cannot exceed 1,000,000.',
            
            'currency.in' => 'Currency must be one of: ' . implode(', ', config('services.pesapal.supported_currencies', ['KES', 'USD'])),
            'currency.size' => 'Currency code must be exactly 3 characters.',
            
            'description.required' => 'Payment description is required.',
            'description.min' => 'Payment description must be at least 5 characters.',
            'description.max' => 'Payment description cannot exceed 255 characters.',
            
            'payment_type.required' => 'Payment type is required.',
            'payment_type.in' => 'Invalid payment type selected.',
            
            'payable_type.in' => 'Invalid payable type specified.',
            'payable_id.integer' => 'Payable ID must be a valid number.',
            'payable_id.min' => 'Payable ID must be greater than 0.',
            
            'recipient_id.exists' => 'Selected recipient does not exist.',
            
            'country_code.size' => 'Country code must be exactly 2 characters.',
            'address_line_1.max' => 'Address line 1 cannot exceed 100 characters.',
            'address_line_2.max' => 'Address line 2 cannot exceed 100 characters.',
            'city.max' => 'City name cannot exceed 50 characters.',
            'state.max' => 'State name cannot exceed 50 characters.',
            'postal_code.max' => 'Postal code cannot exceed 20 characters.'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate payable relationship
            if ($this->filled('payable_type') && $this->filled('payable_id')) {
                $payableType = $this->input('payable_type');
                $payableId = $this->input('payable_id');
                
                if (class_exists($payableType)) {
                    $exists = $payableType::where('id', $payableId)->exists();
                    if (!$exists) {
                        $validator->errors()->add('payable_id', 'The specified payable record does not exist.');
                    }
                } else {
                    $validator->errors()->add('payable_type', 'Invalid payable type specified.');
                }
            }

            // Validate that payable_type and payable_id are both provided or both null
            if ($this->filled('payable_type') && !$this->filled('payable_id')) {
                $validator->errors()->add('payable_id', 'Payable ID is required when payable type is specified.');
            }
            
            if ($this->filled('payable_id') && !$this->filled('payable_type')) {
                $validator->errors()->add('payable_type', 'Payable type is required when payable ID is specified.');
            }

            // Validate minimum amounts for different payment types
            $paymentType = $this->input('payment_type');
            $amount = $this->input('amount');
            
            if ($paymentType && $amount) {
                $minimumAmounts = [
                    'Scholarship Support' => 100, // Minimum 100 KES
                    'Material Donation' => 50,
                    'General Donation' => 10,
                    'Application Fee' => 500,
                    'Promotion Fee' => 1000
                ];
                
                $minimumAmount = $minimumAmounts[$paymentType] ?? 1;
                
                if ($amount < $minimumAmount) {
                    $validator->errors()->add('amount', "Minimum amount for {$paymentType} is {$minimumAmount}.");
                }
            }
        });
    }

    /**
     * Get the validated data with defaults applied.
     */
    public function validatedWithDefaults(): array
    {
        $validated = $this->validated();
        
        // Apply default currency if not provided
        if (!isset($validated['currency'])) {
            $validated['currency'] = config('services.pesapal.default_currency', 'KES');
        }
        
        // Apply default country code if not provided
        if (!isset($validated['country_code'])) {
            $validated['country_code'] = 'KE';
        }
        
        return $validated;
    }
}