<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $vendor_id
 * @property string $total
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FoodOrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Vendor $vendor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodOrder whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodOrder whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodOrder whereVendorId($value)
 * @mixin \Eloquent
 */
class FoodOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vendor_id',
        'total',
        'status',
    ];

    public function items()
    {
        return $this->hasMany(FoodOrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
