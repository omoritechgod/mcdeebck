<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $vendor_id
 * @property string $name
 * @property string|null $description
 * @property string $price
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Vendor $vendor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodMenu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodMenu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodMenu query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodMenu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodMenu whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodMenu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodMenu whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodMenu whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodMenu wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodMenu whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodMenu whereVendorId($value)
 * @mixin \Eloquent
 */
class FoodMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'name',
        'slug',
        'description',
        'price',
        'image',
        'preparation_time_minutes',
        'category',
        'is_available',
        'image_urls',
        'tags',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'image_urls' => 'array',
        'tags' => 'array',
        'preparation_time_minutes' => 'integer',
    ];

    protected $appends = ['estimated_time'];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function foodVendor()
    {
        return $this->belongsTo(FoodVendor::class, 'vendor_id', 'vendor_id');
    }

    public function orderItems()
    {
        return $this->hasMany(FoodOrderItem::class);
    }

    public function getEstimatedTimeAttribute()
    {
        return $this->preparation_time_minutes . ' mins';
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
