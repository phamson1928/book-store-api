<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'phone'          => 'required|string|max:20',
            'address'        => 'required|string|max:255',
            
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'address.required' => 'Địa chỉ giao hàng là bắt buộc.',
            'address.max' => 'Địa chỉ không được vượt quá 500 ký tự.',
            'items.required' => 'Danh sách sản phẩm là bắt buộc.',
            'items.array' => 'Danh sách sản phẩm phải là một mảng.',
            'items.min' => 'Phải có ít nhất 1 sản phẩm trong đơn hàng.',
            'items.*.book_id.required' => 'ID sách là bắt buộc.',
            'items.*.book_id.exists' => 'Sách không tồn tại.',
            'items.*.quantity.required' => 'Số lượng là bắt buộc.',
            'items.*.quantity.integer' => 'Số lượng phải là số nguyên.',
            'items.*.quantity.min' => 'Số lượng phải lớn hơn 0.',
            'items.*.quantity.max' => 'Số lượng không được vượt quá 100.',
        ];
    }

    /**
     * Custom validation logic
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check for duplicate book_ids in items
            $bookIds = collect($this->items)->pluck('book_id');
            if ($bookIds->count() !== $bookIds->unique()->count()) {
                $validator->errors()->add('items', 'Không được có sách trùng lặp trong đơn hàng.');
            }
        });
    }
}
