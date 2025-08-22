<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceOrder extends Model
{
    protected $fillable = [
        'user_id',
        'vendor_id',           // optional if still used, but keep since it exists
        'service_vendor_id',
        'service_pricing_id',  // newly added
        'amount',
        'status',              // pending_vendor_response, awaiting_payment, paid, completed, declined, vendor_busy
        'notes',
        'deadline',
        'paid_at',
        'completed_at',
    ];

    protected $dates = [
        'deadline',
        'paid_at',
        'completed_at',
        'created_at',
        'updated_at',
    ];

    /** ----------------- Relationships ------------------ */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function serviceVendor()
    {
        return $this->belongsTo(ServiceVendor::class);
    }

    public function servicePricing()
    {
        return $this->belongsTo(ServicePricing::class);
    }
}
