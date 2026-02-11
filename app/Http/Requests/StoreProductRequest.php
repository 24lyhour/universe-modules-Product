<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku'],
            'product_type' => ['nullable', 'in:phone,computer,tablet,accessory,other'],
            'price' => ['required', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'stock' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive,draft,out_of_stock'],
            'is_featured' => ['boolean'],
            'pre_order' => ['boolean'],
            'images' => ['nullable', 'array'],
            'images.*' => ['string', 'url'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'outlet_id' => ['nullable', 'integer', 'exists:outlets,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'price.required' => 'Product price is required.',
            'price.min' => 'Price must be at least 0.',
            'sale_price.lt' => 'Sale price must be less than the regular price.',
            'sku.unique' => 'This SKU is already in use.',
            'stock.required' => 'Stock quantity is required.',
            'stock.min' => 'Stock cannot be negative.',
        ];
    }
}
