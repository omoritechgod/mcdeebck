<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vendor_type',          // individual | business
        'business_name',
        'category',             // rider, mechanic, apartment, product_vendor, service_vendor, food_vendor
        'is_setup_complete',
        'is_verified',
    ];

    protected $appends = ['is_live']; // automatically append is_live to JSON

    /**
     * Get the user that owns this vendor profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Rider profile (if vendor is a rider).
     */
    public function rider()
    {
        return $this->hasOne(Rider::class);
    }

    /**
     * Mechanic profile (if vendor is a mechanic).
     */
    public function mechanic()
    {
        return $this->hasOne(Mechanic::class);
    }

    /**
     * Service Apartment profile (if vendor is an apartment owner).
     */
    public function serviceApartment()
    {
        return $this->hasOne(ServiceApartment::class);
    }

    /**
     * Product vendor profile.
     */
    public function productVendor()
    {
        return $this->hasOne(ProductVendor::class);
    }

    /**
     * General service vendor profile.
     */
    public function serviceVendor()
    {
        return $this->hasOne(ServiceVendor::class);
    }
        public function serviceProfile()
    {
        return $this->hasOne(ServiceVendor::class, 'vendor_id');
    }



    /**
     * Food vendor profile.
     */
    public function foodVendor()
    {
        return $this->hasOne(FoodVendor::class);
    }

    /**
     * Check if vendor is LIVE: verified and phone is verified.
     */
    public function getIsLiveAttribute()
    {
        return $this->is_verified && $this->user && !is_null($this->user->phone_verified_at);
    }
    public function listings()
    {
        return $this->hasMany(Listing::class);
    }


    /**
     * Query scope for only LIVE vendors
     */
    public function scopeLive($query)
    {
        return $query->where('is_verified', 1)
                     ->whereHas('user', function ($q) {
                         $q->whereNotNull('phone_verified_at');
                     });
    }
}
