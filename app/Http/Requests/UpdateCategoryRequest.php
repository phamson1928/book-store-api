<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
        $categoryId = $this->route('category'); // Get ID from route parameter
        
        return [
            'name' => 'sometimes|required|string|max:255|unique:categories,name,' . $categoryId,
            'description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên thể loại là bắt buộc.',
            'name.max' => 'Tên thể loại không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên thể loại này đã tồn tại.',
            'description.max' => 'Mô tả không được vượt quá 1000 ký tự.',
        ];
    }
}
