<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:255',
            'author' => 'nullable|string|max:255',
            'author_id' => 'nullable|exists:authors,id',
            'price' => 'sometimes|required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'publication_date' => 'sometimes|required|date',
            'description' => 'sometimes|required|string',
            'language' => 'sometimes|required|string|max:100',
            'category_id' => 'sometimes|required|exists:categories,id',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'new_best_seller' => 'sometimes|required|boolean',
            'weight_in_grams' => 'sometimes|required|integer|min:1',
            'packaging_size_cm' => 'sometimes|required|string|max:50',
            'number_of_pages' => 'sometimes|required|integer|min:1',
            'form' => 'sometimes|required|string|max:100',
            'state' => 'sometimes|required|in:available,out_of_stock',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề sách là bắt buộc.',
            'price.required' => 'Giá sách là bắt buộc.',
            'price.min' => 'Giá sách phải lớn hơn 0.',
            'image.image' => 'File phải là hình ảnh.',
            'image.mimes' => 'Hình ảnh phải có định dạng: jpg, jpeg, png, webp.',
            'image.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
            'category_id.required' => 'Thể loại sách là bắt buộc.',
            'category_id.exists' => 'Thể loại sách không tồn tại.',
            'author_id.exists' => 'Tác giả không tồn tại.',
            'discount_price.lt' => 'Giá khuyến mãi phải nhỏ hơn giá gốc.',
            'state.in' => 'Trạng thái sách phải là: available hoặc out_of_stock.',
        ];
    }
}
