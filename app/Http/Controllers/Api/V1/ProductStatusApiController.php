<?php

namespace Modules\Product\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Product\Actions\UpdateProductStatusAction;
use Modules\Product\Actions\UpdateProductStockAction;
use Modules\Product\Http\Resources\ProductResource;
use Modules\Product\Models\Product;

class ProductStatusApiController extends Controller
{
    public function __construct(
        protected UpdateProductStatusAction $statusAction,
        protected UpdateProductStockAction $stockAction
    ) {}

    /**
     * Activate a product.
     */
    public function activate(Product $product): JsonResponse
    {
        $product = $this->statusAction->activate($product);

        return response()->json([
            'message' => 'Product activated successfully.',
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Deactivate a product.
     */
    public function deactivate(Product $product): JsonResponse
    {
        $product = $this->statusAction->deactivate($product);

        return response()->json([
            'message' => 'Product deactivated successfully.',
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Set product as draft.
     */
    public function setDraft(Product $product): JsonResponse
    {
        $product = $this->statusAction->setDraft($product);

        return response()->json([
            'message' => 'Product set to draft.',
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(Product $product): JsonResponse
    {
        $product = $this->statusAction->toggleFeatured($product);

        $status = $product->is_featured ? 'featured' : 'unfeatured';

        return response()->json([
            'message' => "Product {$status} successfully.",
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Update product stock.
     */
    public function updateStock(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
            'operation' => ['sometimes', 'in:set,add,subtract'],
        ]);

        $operation = $request->input('operation', 'set');
        $quantity = $request->integer('quantity');

        $product = match ($operation) {
            'add' => $this->stockAction->add($product, $quantity),
            'subtract' => $this->stockAction->subtract($product, $quantity),
            default => $this->stockAction->execute($product, $quantity),
        };

        return response()->json([
            'message' => 'Product stock updated successfully.',
            'data' => new ProductResource($product),
        ]);
    }
}
