<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuthorRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:1|max:150',
            'gender' => 'required|string|in:Nam,Nữ',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'description' => 'required|string|max:1000',
            'nationality' => 'required|string',
            'total_work' => 'required|integer'
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
            'image.required' => 'Hình ảnh tác giả là bắt buộc.',
            'image.image' => 'File phải là hình ảnh.',
            'image.mimes' => 'Hình ảnh phải có định dạng: jpg, jpeg, png, webp.',
            'image.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
            'description.required' => 'Mô tả tác giả là bắt buộc.',
            'description.max' => 'Mô tả không được vượt quá 1000 ký tự.',
        ];
    }
}
