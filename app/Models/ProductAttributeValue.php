<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductAttributeValue extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'product_attribute_values';

    protected $fillable = [
        'uuid',
        'attribute_id',
        'value',
        'label',
        'color_code',
        'image_url',
        'price_adjustment',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_adjustment' => 'decimal:2',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ProductAttributeValue $value) {
            if (empty($value->uuid)) {
                $value->uuid = (string) Str::uuid();
            }
            if (empty($value->label)) {
                $value->label = $value->value;
            }
        });
    }

    /**
     * Relation to the parent attribute.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id');
    }

    /**
     * Relation to variants using this value.
     */
    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_attribute_values', 'attribute_value_id', 'variant_id')
            ->withTimestamps();
    }

    /**
     * Scope for active values.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered values.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get display label.
     */
    public function getDisplayLabelAttribute(): string
    {
        return $this->label ?? $this->value;
    }

    /**
     * Check if this is a color type value.
     */
    public function isColor(): bool
    {
        return $this->attribute?->type === 'color' && !empty($this->color_code);
    }

    /**
     * Get formatted price adjustment.
     */
    public function getFormattedPriceAdjustmentAttribute(): string
    {
        if ($this->price_adjustment == 0) {
            return '';
        }

        $sign = $this->price_adjustment > 0 ? '+' : '';
        return $sign . number_format($this->price_adjustment, 2);
    }
}
