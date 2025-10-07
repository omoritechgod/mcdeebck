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

    const STATUS_PENDING_PAYMENT = 'pending_payment';
    const STATUS_AWAITING_VENDOR = 'awaiting_vendor';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY_FOR_PICKUP = 'ready_for_pickup';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_PICKED_UP = 'picked_up';
    const STATUS_ON_THE_WAY = 'on_the_way';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_DISPUTED = 'disputed';

    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    const DELIVERY_METHOD_DELIVERY = 'delivery';
    const DELIVERY_METHOD_PICKUP = 'pickup';
    const DELIVERY_METHOD_OFFLINE_RIDER = 'offline_rider';

    protected $fillable = [
        'user_id',
        'vendor_id',
        'rider_id',
        'total',
        'tip_amount',
        'delivery_fee',
        'commission_amount',
        'payment_status',
        'payment_reference',
        'status',
        'delivery_method',
        'shipping_address',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'tip_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'shipping_address' => 'array',
    ];

    protected $appends = ['can_show_contacts'];

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

    public function foodVendor()
    {
        return $this->belongsTo(FoodVendor::class, 'vendor_id', 'vendor_id');
    }

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    public function getCanShowContactsAttribute()
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PAID);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
