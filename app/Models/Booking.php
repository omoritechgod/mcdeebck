<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'listing_id',
        'vendor_id',
        'check_in_date',
        'check_out_date',
        'nights',
        'total_price',
        'status',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
