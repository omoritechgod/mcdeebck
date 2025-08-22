<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $balance
 * @property string $currency
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WalletTransaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereUserId($value)
 * @mixin \Eloquent
 */
class Wallet extends Model
{
    protected $fillable = ['user_id', 'balance', 'currency'];

    
    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
