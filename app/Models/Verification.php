<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type  // nin or cac
 * @property string|null $value // optional: raw ID number
 * @property string|null $document_url // path to uploaded file
 * @property string $status // pending, approved, rejected
 * @property Carbon|null $verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\User $user
 */
class Verification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'value',
        'document_url',
        'status',
        'verified_at',
        'rejection_reason',
    ];


    protected $casts = [
        'verified_at' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // In app/Models/Verification.php
    public function vendor()
    {
        return $this->hasOne(Vendor::class, 'user_id', 'user_id');
    }


    /**
     * The vendor profile linked to this KYC.
     */

}
