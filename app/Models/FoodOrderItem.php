<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $food_order_id
 * @property int $food_menu_id
 * @property int $quantity
 * @property string $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\FoodMenu $food
 * @property-read \App\Models\FoodOrder|null $order
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodOrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodOrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodOrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodOrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodOrderItem whereFoodMenuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodOrderItem whereFoodOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodOrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodOrderItem wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodOrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodOrderItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FoodOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'food_order_id',
        'food_menu_id',
        'quantity',
        'price',
        'total_price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function menuItem()
    {
        return $this->belongsTo(FoodMenu::class, 'food_menu_id');
    }

    public function food()
    {
        return $this->belongsTo(FoodMenu::class, 'food_menu_id');
    }

    public function order()
    {
        return $this->belongsTo(FoodOrder::class, 'food_order_id');
    }
}
