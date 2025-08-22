<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property-read \App\Models\Vendor|null $vendor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceVendor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceVendor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceVendor query()
 * @mixin \Eloquent
 */
class ServiceVendor extends Model
{
    protected $fillable = [
        'vendor_id',
        'service_name',
        'description',
        'location',
        'phone',
        'rating',
        'ratings_count'
    ];

    // Each service vendor belongs to a vendor account
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    // Service vendor can have many orders
    public function orders()
    {
        return $this->hasMany(ServiceOrder::class, 'service_vendor_id');
    }
        public function pricings()
    {
        return $this->hasMany(ServicePricing::class);
    }

    public function serviceOrders()
    {
        return $this->hasMany(ServiceOrder::class);
    }
}