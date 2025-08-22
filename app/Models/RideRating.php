<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $ride_id
 * @property int $user_id
 * @property int $rating
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ride $ride
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RideRating newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RideRating newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RideRating query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RideRating whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RideRating whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RideRating whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RideRating whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RideRating whereRideId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RideRating whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RideRating whereUserId($value)
 * @mixin \Eloquent
 */
class RideRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'ride_id',
        'user_id',
        'rating',
        'comment',
    ];

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
