<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'title',
        'description',
        'images', // array of full URLs (Cloudinary)
        'price',
        'stock_quantity',
        'category_id',
        'condition',
        'allow_pickup',
        'allow_shipping',
        'status',
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'allow_pickup' => 'boolean',
        'allow_shipping' => 'boolean',
    ];

    /**
     * Relationship to vendor.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Query scope: only products visible on public marketplace.
     *
     * Conditions:
     *  - product.status = 'active'
     *  - vendor.is_verified = 1
     *  - vendor->user.phone_verified_at IS NOT NULL
     */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->whereHas('vendor', function (Builder $q) {
                $q->where('is_verified', 1)
                  ->whereHas('user', function (Builder $u) {
                      $u->whereNotNull('phone_verified_at');
                  });
            });
    }

    /**
     * Convenience: return first image (thumbnail) or null.
     */
    public function getThumbnailAttribute()
    {
        return $this->images[0] ?? null;
    }
}
