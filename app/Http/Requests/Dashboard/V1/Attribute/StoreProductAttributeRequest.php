<?php

namespace Modules\Product\Http\Requests\Dashboard\V1\Attribute;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductAttributeRequest extends FormRequest
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
            'type' => ['required', 'in:select,color,button'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'values' => ['nullable', 'array'],
            'values.*.value' => ['required', 'string', 'max:255'],
            'values.*.label' => ['nullable', 'string', 'max:255'],
            'values.*.color_code' => ['nullable', 'string', 'max:20'],
            'values.*.price_adjustment' => ['nullable', 'numeric'],
            'values.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'values.*.is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Attribute name is required.',
            'type.required' => 'Attribute type is required.',
            'type.in' => 'Attribute type must be select, color, or button.',
            'values.*.value.required' => 'Each value must have a value field.',
        ];
    }
}
