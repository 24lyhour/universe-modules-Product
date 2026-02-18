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
            'price_adjustment' => ['nullable', 'numeric'],
            'max_quantity' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_required' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'max_quantity.min' => 'Max quantity must be at least 1.',
        ];
    }
}
