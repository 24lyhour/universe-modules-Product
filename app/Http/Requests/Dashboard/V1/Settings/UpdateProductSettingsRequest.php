<?php

namespace Modules\Product\Http\Requests\Dashboard\V1\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductSettingsRequest extends FormRequest
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
            'auto_generate_sku' => ['required', 'boolean'],
            'sku_prefix' => ['nullable', 'string', 'max:10'],
            'sku_separator' => ['nullable', 'string', 'max:3'],
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'auto_generate_sku.required' => 'The auto generate SKU setting is required.',
            'sku_prefix.max' => 'The SKU prefix must not exceed 10 characters.',
            'sku_separator.max' => 'The SKU separator must not exceed 3 characters.',
            'low_stock_threshold.required' => 'The low stock threshold is required.',
            'low_stock_threshold.min' => 'The low stock threshold must be at least 0.',
            'low_stock_threshold.max' => 'The low stock threshold must not exceed 1000.',
        ];
    }
}
