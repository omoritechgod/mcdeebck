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
        'description',
        'price',
        'image',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
