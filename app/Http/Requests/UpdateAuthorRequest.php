<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAuthorRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:255',
            'age' => 'sometimes|required|integer|min:1|max:150',
            'gender' => 'sometimes|required|string|in:male,female,other',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'description' => 'sometimes|required|string|max:1000',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên tác giả là bắt buộc.',
            'name.max' => 'Tên tác giả không được vượt quá 255 ký tự.',
            'age.required' => 'Tuổi tác giả là bắt buộc.',
            'age.integer' => 'Tuổi phải là số nguyên.',
            'age.min' => 'Tuổi phải lớn hơn 0.',
            'age.max' => 'Tuổi không được vượt quá 150.',
            'gender.required' => 'Giới tính là bắt buộc.',
            'gender.in' => 'Giới tính phải là: male, female, hoặc other.',
            'image.image' => 'File phải là hình ảnh.',
            'image.mimes' => 'Hình ảnh phải có định dạng: jpg, jpeg, png, webp.',
            'image.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
            'description.required' => 'Mô tả tác giả là bắt buộc.',
            'description.max' => 'Mô tả không được vượt quá 1000 ký tự.',
        ];
    }
}
