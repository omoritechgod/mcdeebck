<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'title',
        'description',
        'location',
        'price_per_night',
        'type',
        'images',
        'is_verified',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    /**
     * The vendor who owns this listing.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
