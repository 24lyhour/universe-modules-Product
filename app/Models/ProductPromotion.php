<?php

namespace Modules\Product\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductPromotion extends Model
{
    use HasFactory, HasUuid;

    /**
     * The table associated with the model.
     */
    protected $table = 'product_promotion';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'product_id',
        'product_promotion_type',
        'percentage',
        'price_discounts',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'percentage'      => 'decimal:2',
        'price_discounts' => 'decimal:2',
        'is_active'       => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ProductPromotion $promotion) {
            if (empty($promotion->uuid)) {
                $promotion->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Relation to the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate the discounted price for a given original price.
     */
    public function calculateDiscountedPrice(float $originalPrice): float
    {
        if ($this->percentage) {
            return $originalPrice - ($originalPrice * ($this->percentage / 100));
        }

        if ($this->price_discounts) {
            return max(0, $originalPrice - $this->price_discounts);
        }

        return $originalPrice;
    }

    /**
     * Get the discount amount for a given original price.
     */
    public function getDiscountAmount(float $originalPrice): float
    {
        return $originalPrice - $this->calculateDiscountedPrice($originalPrice);
    }

    /**
     * Check if promotion is currently active.
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Scope for active promotions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
