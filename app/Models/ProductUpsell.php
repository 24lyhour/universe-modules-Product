<?php

namespace Modules\Product\Models;

use App\Traits\BelongsToProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductUpsell extends Model
{
    use BelongsToProduct;
    /**
     * Upsell types.
     */
    public const TYPE_UPSELL = 'upsell';
    public const TYPE_DOWNSELL = 'downsell';
    public const TYPE_CROSS_SELL = 'cross_sell';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'product_id',
        'upsell_product_id',
        'type',
        'discount_percentage',
        'sort_order',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'discount_percentage' => 'decimal:2',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ProductUpsell $upsell) {
            if (empty($upsell->uuid)) {
                $upsell->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the main product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the upsell/downsell/cross-sell product.
     */
    public function upsellProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'upsell_product_id');
    }

    /**
     * Scope for active upsells.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for upsells only.
     */
    public function scopeUpsells($query)
    {
        return $query->ofType(self::TYPE_UPSELL);
    }

    /**
     * Scope for downsells only.
     */
    public function scopeDownsells($query)
    {
        return $query->ofType(self::TYPE_DOWNSELL);
    }

    /**
     * Scope for cross-sells only.
     */
    public function scopeCrossSells($query)
    {
        return $query->ofType(self::TYPE_CROSS_SELL);
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_UPSELL => 'Upsell',
            self::TYPE_DOWNSELL => 'Downsell',
            self::TYPE_CROSS_SELL => 'Cross-sell',
            default => ucfirst($this->type),
        };
    }

    /**
     * Calculate discounted price if applicable.
     */
    public function getDiscountedPriceAttribute(): ?float
    {
        if (!$this->discount_percentage || !$this->upsellProduct) {
            return null;
        }

        $originalPrice = $this->upsellProduct->effective_price;
        return $originalPrice - ($originalPrice * ($this->discount_percentage / 100));
    }
}
