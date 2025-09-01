<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
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
            'address' => 'sometimes|required|string|max:500',
            'state' => 'sometimes|required|string|in:Chờ xác nhận,Đang vận chuyển,Đã giao,Đã hủy',
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
            'state.required' => 'Trạng thái đơn hàng là bắt buộc.',
            'state.in' => 'Trạng thái phải hợp lệ.',
        ];
    }
}
