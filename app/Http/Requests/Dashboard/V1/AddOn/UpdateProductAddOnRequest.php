<?php

namespace Modules\Product\Http\Requests\Dashboard\V1\AddOn;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductAddOnRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'price_adjustment' => ['required', 'numeric'],
            'max_quantity' => ['required', 'integer', 'min:1'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_required' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Add-on name is required.',
            'name.max' => 'Name must be 255 characters or less.',
            'price_adjustment.required' => 'Price adjustment is required.',
            'price_adjustment.numeric' => 'Price adjustment must be a number.',
            'max_quantity.required' => 'Max quantity is required.',
            'max_quantity.min' => 'Max quantity must be at least 1.',
            'is_required.required' => 'Required field is required.',
            'is_active.required' => 'Active field is required.',
        ];
    }
}
