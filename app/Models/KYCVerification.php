<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KYCVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vendor_id',
        'document_type',
        'document_url',
        'status',
        'rejection_reason',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    /**
     * The user who submitted the KYC.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The vendor profile linked to this KYC.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
