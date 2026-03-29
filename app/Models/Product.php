<?php

namespace Modules\Product\Models;

use App\Traits\BelongsToOutlet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Product\Database\Factories\ProductFactory;
use Modules\Menu\Models\Category;
use Modules\Product\Models\ProductType;
use Modules\Outlet\Models\Outlet;
use App\Models\User;

class Product extends Model
{
    use HasFactory, SoftDeletes, BelongsToOutlet;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'tenant_id',
        'tenant_type',
        'outlet_id',
        'outlet_type',
        'description',
        'sku',
        'price',
        'product_type',
        'product_type_id',
        'purchase_price',
        'sale_price',
        'stock',
        'upsale_id',
        'down_sale_id',
        'varitations_id',
        'add_ons_id',
        'low_stock_threshold',
        'status',
        'is_featured',
        'pre_order',
        'images',
        'category_id',
        'brand_id',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'stock' => 'integer',
            'low_stock_threshold' => 'integer',
            'is_featured' => 'boolean',
            'pre_order' => 'boolean',
            'images' => 'array',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Product $product) {
            if (empty($product->uuid)) {
                $product->uuid = (string) Str::uuid();
            }
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::updating(function (Product $product) {
            if ($product->isDirty('name') && !$product->isDirty('slug')) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    /**
     * Relation to the category (direct foreign key).
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Many-to-many relationship with menu categories via pivot table.
     */
    public function menuCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'menu_category_products', 'product_id', 'category_id')
            ->withPivot('price_override', 'sort_order', 'is_available')
            ->withTimestamps();
    }

    /**
     * Relation to the outlet.
     */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    /**
     * Relation to the brand.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * Relation to the user who created this product.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation to the user who last updated this product.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relation to the upsell product.
     */
    public function upsell(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'upsale_id');
    }

    /**
     * Relation to the downsell product.
     */
    public function downsell(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'down_sale_id');
    }

    /**
     * Get products that have this product as upsell.
     */
    public function upsellFor(): HasMany
    {
        return $this->hasMany(Product::class, 'upsale_id');
    }

    /**
     * Get products that have this product as downsell.
     */
    public function downsellFor(): HasMany
    {
        return $this->hasMany(Product::class, 'down_sale_id');
    }

    /**
     * Relation to product upsells (using ProductUpsell model).
     */
    public function productUpsells(): HasMany
    {
        return $this->hasMany(ProductUpsell::class, 'product_id')
            ->orderBy('sort_order');
    }

    /**
     * Get upsell products (higher-priced alternatives).
     */
    public function upsellProducts(): HasMany
    {
        return $this->productUpsells()
            ->where('type', ProductUpsell::TYPE_UPSELL)
            ->where('is_active', true);
    }

    /**
     * Get downsell products (lower-priced alternatives).
     */
    public function downsellProducts(): HasMany
    {
        return $this->productUpsells()
            ->where('type', ProductUpsell::TYPE_DOWNSELL)
            ->where('is_active', true);
    }

    /**
     * Get cross-sell products (complementary products).
     */
    public function crossSellProducts(): HasMany
    {
        return $this->productUpsells()
            ->where('type', ProductUpsell::TYPE_CROSS_SELL)
            ->where('is_active', true);
    }

    /**
     * Get products that have this product as an upsell/downsell/cross-sell.
     */
    public function usedAsUpsellIn(): HasMany
    {
        return $this->hasMany(ProductUpsell::class, 'upsell_product_id');
    }

    /**
     * Relation to product variants.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    /**
     * Relation to active variants only.
     */
    public function activeVariants(): HasMany
    {
        return $this->variants()->where('is_active', true);
    }

    /**
     * Relation to product attributes.
     */
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(ProductAttribute::class, 'product_product_attributes', 'product_id', 'attribute_id')
            ->withPivot('sort_order', 'is_required')
            ->withTimestamps()
            ->orderBy('pivot_sort_order');
    }

    /**
     * Relation to product add-ons.
     */
    public function addOns(): HasMany
    {
        return $this->hasMany(ProductAddOn::class, 'product_id')
            ->orderBy('sort_order');
    }

    /**
     * Relation to active add-ons only.
     */
    public function activeAddOns(): HasMany
    {
        return $this->addOns()->where('is_active', true);
    }

    /**
     * Get products that have this product as an add-on.
     */
    public function usedAsAddOnIn(): HasMany
    {
        return $this->hasMany(ProductAddOn::class, 'add_on_product_id');
    }

    /**
     * Relation to the product type.
     */
    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    /**
     * Check if product has add-ons.
     */
    public function hasAddOns(): bool
    {
        return $this->addOns()->exists();
    }

    /**
     * Get the default variant.
     */
    public function defaultVariant(): ?ProductVariant
    {
        return $this->variants()->where('is_default', true)->first()
            ?? $this->variants()->first();
    }

    /**
     * Check if product has variants.
     */
    public function hasVariants(): bool
    {
        return $this->variants()->exists();
    }

    /**
     * Check if product has attributes.
     */
    public function hasAttributes(): bool
    {
        return $this->attributes()->exists();
    }

    /**
     * Get total stock across all variants.
     */
    public function getTotalVariantStockAttribute(): int
    {
        if (!$this->hasVariants()) {
            return $this->stock;
        }

        return $this->variants()->sum('stock');
    }

    /**
     * Get price range for products with variants.
     */
    public function getPriceRangeAttribute(): array
    {
        if (!$this->hasVariants()) {
            return ['min' => $this->effective_price, 'max' => $this->effective_price];
        }

        $prices = $this->activeVariants()
            ->get()
            ->map(fn ($v) => $v->display_price);

        return [
            'min' => $prices->min() ?? $this->effective_price,
            'max' => $prices->max() ?? $this->effective_price,
        ];
    }

    /**
     * Scope for active products.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for featured products.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for in-stock products.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Scope for low stock products.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'low_stock_threshold')
            ->where('stock', '>', 0);
    }

    /**
     * Check if product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    /**
     * Check if product is low on stock.
     */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->low_stock_threshold && $this->stock > 0;
    }

    /**
     * Get the effective price (sale price if available, otherwise regular price).
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->sale_price ?? $this->price;
    }

    /**
     * Check if product is on sale.
     */
    public function isOnSale(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    /**
     * Get discount percentage.
     */
    public function getDiscountPercentageAttribute(): ?float
    {
        if (!$this->isOnSale() || $this->price == 0) {
            return null;
        }

        return round((($this->price - $this->sale_price) / $this->price) * 100, 1);
    }

    /**
     * Relation to cart items.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Relation to order items.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * relation to the review
     */
    public function Review() : HasMany
    {
    
        return $this->HasMany(Review::class);
    }
}
