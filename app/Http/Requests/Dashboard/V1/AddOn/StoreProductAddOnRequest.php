<?php

namespace Modules\Product\Http\Requests\Dashboard\V1\AddOn;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductAddOnRequest extends FormRequest
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
        $productId = $this->route('product')->id;

        return [
            'add_on_product_id' => [
                'required',
                'integer',
                'exists:products,id',
                'different:product_id',
                Rule::unique('product_add_ons')
                    ->where('product_id', $productId),
            ],
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
            'add_on_product_id.required' => 'Please select a product.',
            'add_on_product_id.exists' => 'The selected product does not exist.',
            'add_on_product_id.unique' => 'This product is already added as an add-on.',
            'add_on_product_id.different' => 'A product cannot be its own add-on.',
            'max_quantity.min' => 'Max quantity must be at least 1.',
        ];
    }
}
