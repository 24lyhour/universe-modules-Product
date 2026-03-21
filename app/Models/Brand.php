<?php

namespace Modules\Product\Models;

use App\Traits\BelongsToOutlet;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Outlet\Models\Outlet;
use Modules\Product\Database\Factories\BrandFactory;

class Brand extends Model
{
    use HasFactory;
    use HasUuid;
    use BelongsToOutlet;
    use SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'brands';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'logo',
        'website',
        'outlet_id',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the factory for the model.
     */
    protected static function newFactory(): BrandFactory
    {
        return BrandFactory::new();
    }

    /**
     * Get the products for this brand.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'brand_id');
    }

    /**
     * Get the outlet that owns this brand.
     */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    /**
     * Scope active brands.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
