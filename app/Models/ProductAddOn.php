<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductAddOn extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'product_add_ons';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'product_id',
        'add_on_product_id',
        'price_adjustment',
        'max_quantity',
        'sort_order',
        'is_required',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'price_adjustment' => 'decimal:2',
            'max_quantity' => 'integer',
            'sort_order' => 'integer',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ProductAddOn $addOn) {
            if (empty($addOn->uuid)) {
                $addOn->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the main product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the add-on product.
     */
    public function addOnProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope for active add-ons.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for required add-ons.
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope for optional add-ons.
     */
    public function scopeOptional($query)
    {
        return $query->where('is_required', false);
    }

    /**
     * Get the final price (add-on product price + adjustment).
     */
    public function getFinalPriceAttribute(): float
    {
        if (!$this->addOnProduct) {
            return (float) $this->price_adjustment;
        }

        return (float) ($this->addOnProduct->effective_price + $this->price_adjustment);
    }

    /**
     * Get formatted price adjustment.
     */
    public function getFormattedPriceAdjustmentAttribute(): string
    {
        if ($this->price_adjustment == 0) {
            return 'No adjustment';
        }

        $prefix = $this->price_adjustment > 0 ? '+' : '';
        return $prefix . '$' . number_format(abs($this->price_adjustment), 2);
    }
}
