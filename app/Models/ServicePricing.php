<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePricing extends Model
{
    protected $fillable = [
        'service_vendor_id',
        'title',
        'description',
        'price',
    ];

    public function serviceVendor()
    {
        return $this->belongsTo(ServiceVendor::class);
    }
}
