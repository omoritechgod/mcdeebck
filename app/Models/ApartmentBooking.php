<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $apartment_id
 * @property \Illuminate\Support\Carbon $check_in
 * @property \Illuminate\Support\Carbon $check_out
 * @property string $total_price
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Apartment $apartment
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApartmentBooking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApartmentBooking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApartmentBooking query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApartmentBooking whereApartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApartmentBooking whereCheckIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApartmentBooking whereCheckOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApartmentBooking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApartmentBooking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApartmentBooking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApartmentBooking whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApartmentBooking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApartmentBooking whereUserId($value)
 * @mixin \Eloquent
 */
class ApartmentBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'apartment_id',
        'check_in',
        'check_out',
        'total_price',
        'status',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
