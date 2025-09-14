<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiscountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'discount_percent' => 'required|integer|min:1|max:100',
            'active' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'discount_percent.required' => 'The discount percent field is required.',
            'discount_percent.integer' => 'The discount percent must be an integer.',
            'discount_percent.min' => 'The discount percent must be at least 1.',
            'discount_percent.max' => 'The discount percent may not be greater than 100.',
            'active.boolean' => 'The active field must be a boolean value.',
        ];
    }
}
