<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property-read \App\Models\User|null $receiver
 * @property-read \App\Models\User|null $sender
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P2PTransfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P2PTransfer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P2PTransfer query()
 * @mixin \Eloquent
 */
class P2PTransfer extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'amount', 'status'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
