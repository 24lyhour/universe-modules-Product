<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductAttribute extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'product_attributes';

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'type',
        'description',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ProductAttribute $attribute) {
            if (empty($attribute->uuid)) {
                $attribute->uuid = (string) Str::uuid();
            }
            if (empty($attribute->slug)) {
                $attribute->slug = Str::slug($attribute->name);
            }
        });
    }

    /**
     * Relation to attribute values.
     */
    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id')
            ->orderBy('sort_order');
    }

    /**
     * Relation to products using this attribute.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_product_attributes', 'attribute_id', 'product_id')
            ->withPivot('sort_order', 'is_required')
            ->withTimestamps();
    }

    /**
     * Relation to the user who created this attribute.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Relation to the user who updated this attribute.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Scope for active attributes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered attributes.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Check if attribute has values.
     */
    public function hasValues(): bool
    {
        return $this->values()->exists();
    }

    /**
     * Get active values only.
     */
    public function activeValues(): HasMany
    {
        return $this->values()->where('is_active', true);
    }
}
