<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $vendor_id
 * @property string $business_name
 * @property string $specialty
 * @property string $location
 * @property string $contact_phone
 * @property string|null $contact_email
 * @property string|null $description
 * @property string|null $logo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Vendor $vendor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereBusinessName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereContactEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereSpecialty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereVendorId($value)
 * @mixin \Eloquent
 */
class FoodVendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'business_name',
        'specialty',
        'cuisines',
        'estimated_preparation_time',
        'location',
        'latitude',
        'longitude',
        'contact_phone',
        'contact_email',
        'description',
        'logo',
        'operating_hours',
        'delivery_radius_km',
        'minimum_order_amount',
        'delivery_fee',
        'is_open',
        'accepts_cash',
        'accepts_card',
        'average_rating',
        'total_reviews',
        'total_orders'
    ];

    protected $casts = [
        'operating_hours' => 'array',
        'cuisines' => 'array',
        'delivery_radius_km' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'is_open' => 'boolean',
        'accepts_cash' => 'boolean',
        'accepts_card' => 'boolean',
        'average_rating' => 'decimal:2',
        'total_reviews' => 'integer',
        'total_orders' => 'integer',
        'estimated_preparation_time' => 'integer',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function menuItems()
    {
        return $this->hasMany(FoodMenu::class, 'vendor_id', 'vendor_id');
    }

    public function orders()
    {
        return $this->hasMany(FoodOrder::class, 'vendor_id', 'vendor_id');
    }

    public function scopeLive($query)
    {
        return $query->whereHas('vendor', function($q) {
            $q->where('is_verified', true)
              ->whereHas('user', function($u) {
                  $u->whereNotNull('phone_verified_at');
              });
        });
    }
}
