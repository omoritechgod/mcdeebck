<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $vendor_id
 * @property string $title
 * @property string|null $description
 * @property string $location
 * @property string $price_per_night
 * @property string $type
 * @property bool $is_verified
 * @property array<array-key, mixed>|null $images
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApartmentBooking> $bookings
 * @property-read int|null $bookings_count
 * @property-read \App\Models\Vendor $vendor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Apartment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Apartment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Apartment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Apartment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Apartment whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Apartment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Apartment whereImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Apartment whereIsVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Apartment whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Apartment wherePricePerNight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Apartment whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Apartment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Apartment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Apartment whereVendorId($value)
 * @mixin \Eloquent
 */
class Apartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'title',
        'description',
        'location',
        'price_per_night',
        'type',
        'is_verified',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
        'is_verified' => 'boolean',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function bookings()
    {
        return $this->hasMany(ApartmentBooking::class);
    }
}
