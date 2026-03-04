<?php

namespace Modules\Product\Http\Requests\Dashboard\V1\ProductType;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductTypeRequest extends FormRequest
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
            'description' => ['nullable', 'string', 'max:1000'],
            'outlet_id' => ['required', 'integer', 'exists:outlets,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product type name is required.',
            'name.max' => 'Product type name must be less than 255 characters.',
            'outlet_id.required' => 'Outlet is required.',
            'outlet_id.exists' => 'Selected outlet does not exist.',
        ];
    }
}
