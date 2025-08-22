<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminTransaction extends Model
{
    protected $fillable = [
        'admin_wallet_id',
        'type',
        'amount',
        'ref',
        'status',
        'description',     // ✅ added
        'related_type',    // ✅ added
        'related_id',      // ✅ added
        'meta'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
    ];

    public function wallet()
    {
        return $this->belongsTo(AdminWallet::class, 'admin_wallet_id');
    }
}
