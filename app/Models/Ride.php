<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $rider_id
 * @property string $status
 * @property string|null $fare
 * @property string $pickup_lat
 * @property string $pickup_lng
 * @property string $dropoff_lat
 * @property string $dropoff_lng
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GpsLog> $gpsLogs
 * @property-read int|null $gps_logs_count
 * @property-read \App\Models\RideRating|null $rating
 * @property-read \App\Models\User $rider
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ride newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ride newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ride query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ride whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ride whereDropoffLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ride whereDropoffLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ride whereFare($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ride whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ride wherePickupLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ride wherePickupLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ride whereRiderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ride whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ride whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ride whereUserId($value)
 * @mixin \Eloquent
 */
class Ride extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rider_id',
        'pickup_lat',
        'pickup_lng',
        'dropoff_lat',
        'dropoff_lng',
        'status',
        'fare',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function gpsLogs()
    {
        return $this->hasMany(GpsLog::class);
    }

    public function rating()
    {
        return $this->hasOne(RideRating::class);
    }
}
