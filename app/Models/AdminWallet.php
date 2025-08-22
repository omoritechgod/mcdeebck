<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminWallet extends Model
{
    protected $fillable = ['name', 'balance', 'currency'];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function transactions()
    {
        return $this->hasMany(AdminTransaction::class);
    }
}
