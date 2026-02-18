<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'product_variants';

    protected $fillable = [
        'uuid',
        'product_id',
        'sku',
        'name',
        'price',
        'purchase_price',
        'sale_price',
        'stock',
        'low_stock_threshold',
        'barcode',
        'weight',
        'images',
        'attribute_values',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'weight' => 'decimal:2',
            'stock' => 'integer',
            'low_stock_threshold' => 'integer',
            'images' => 'array',
            'attribute_values' => 'array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ProductVariant $variant) {
            if (empty($variant->uuid)) {
                $variant->uuid = (string) Str::uuid();
            }
        });

        static::saving(function (ProductVariant $variant) {
            // Auto-generate name from attribute values if not set
            if (empty($variant->name) && $variant->attributeValueRelations()->exists()) {
                $variant->name = $variant->generateName();
            }

            // Cache attribute values as JSON
            if ($variant->attributeValueRelations()->exists()) {
                $variant->attribute_values = $variant->getAttributeValuesArray();
            }
        });
    }

    /**
     * Relation to the parent product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relation to attribute values.
     */
    public function attributeValueRelations(): BelongsToMany
    {
        return $this->belongsToMany(ProductAttributeValue::class, 'product_variant_attribute_values', 'variant_id', 'attribute_value_id')
            ->withTimestamps();
    }

    /**
     * Scope for active variants.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default variant.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope for in-stock variants.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Get the effective price (variant price or product price).
     */
    public function getEffectivePriceAttribute(): float
    {
        if ($this->sale_price !== null) {
            return (float) $this->sale_price;
        }

        if ($this->price !== null) {
            return (float) $this->price;
        }

        return (float) ($this->product?->effective_price ?? 0);
    }

    /**
     * Get the display price (with adjustments from attribute values).
     */
    public function getDisplayPriceAttribute(): float
    {
        $basePrice = $this->effective_price;

        // Add price adjustments from attribute values
        $adjustments = $this->attributeValueRelations()
            ->sum('price_adjustment');

        return $basePrice + $adjustments;
    }

    /**
     * Check if variant is in stock.
     */
    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    /**
     * Check if variant is low on stock.
     */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->low_stock_threshold && $this->stock > 0;
    }

    /**
     * Check if variant is on sale.
     */
    public function isOnSale(): bool
    {
        return $this->sale_price !== null && $this->sale_price < ($this->price ?? $this->product?->price ?? 0);
    }

    /**
     * Generate variant name from attribute values.
     */
    public function generateName(): string
    {
        $productName = $this->product?->name ?? '';
        $values = $this->attributeValueRelations()
            ->with('attribute')
            ->get()
            ->pluck('display_label')
            ->implode(', ');

        return $values ? "{$productName} - {$values}" : $productName;
    }

    /**
     * Get attribute values as array for caching.
     */
    public function getAttributeValuesArray(): array
    {
        return $this->attributeValueRelations()
            ->with('attribute')
            ->get()
            ->mapWithKeys(function ($value) {
                return [$value->attribute->slug => $value->value];
            })
            ->toArray();
    }

    /**
     * Get full SKU (product SKU + variant SKU).
     */
    public function getFullSkuAttribute(): string
    {
        $productSku = $this->product?->sku ?? '';
        return $this->sku ? "{$productSku}-{$this->sku}" : $productSku;
    }

    /**
     * Get variant images or fallback to product images.
     */
    public function getDisplayImagesAttribute(): array
    {
        return !empty($this->images) ? $this->images : ($this->product?->images ?? []);
    }
}
