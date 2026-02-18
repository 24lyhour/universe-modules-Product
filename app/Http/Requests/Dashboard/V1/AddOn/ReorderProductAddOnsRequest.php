<?php

namespace Modules\Product\Http\Requests\Dashboard\V1\AddOn;

use Illuminate\Foundation\Http\FormRequest;

class ReorderProductAddOnsRequest extends FormRequest
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
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:product_add_ons,id'],
            'items.*.sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Items are required.',
            'items.*.id.required' => 'Each item must have an ID.',
            'items.*.id.exists' => 'One or more add-ons do not exist.',
            'items.*.sort_order.required' => 'Each item must have a sort order.',
            'items.*.sort_order.min' => 'Sort order must be at least 0.',
        ];
    }
}
