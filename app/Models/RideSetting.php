<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 
 *
 * @property int $id
 * @property string $base_fare
 * @property string $rate_per_km
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RideSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RideSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RideSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RideSetting whereBaseFare($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RideSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RideSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RideSetting whereRatePerKm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RideSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RideSetting extends Model
{
    use HasFactory;

    protected $fillable = ['base_fare', 'rate_per_km'];
}
