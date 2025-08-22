<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 
 *
 * @property int $id
 * @property int $ride_id
 * @property string $lat
 * @property string $lng
 * @property string $logged_at
 * @property-read \App\Models\Ride $ride
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GpsLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GpsLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GpsLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GpsLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GpsLog whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GpsLog whereLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GpsLog whereLoggedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GpsLog whereRideId($value)
 * @mixin \Eloquent
 */
class GpsLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['ride_id', 'lat', 'lng', 'logged_at'];

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }
}
