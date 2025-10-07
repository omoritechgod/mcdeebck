<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Vendor;

/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $vendor_id
 * @property string|null $vehicle_type
 * @property string|null $license_number
 * @property int|null $experience_years
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @property-read Vendor $vendor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rider newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rider newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rider query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rider whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rider whereExperienceYears($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rider whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rider whereLicenseNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rider whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rider whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rider whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rider whereVehicleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rider whereVendorId($value)
 * @mixin \Eloquent
 */
class Rider extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'user_id',
        'vendor_id',
        'status',
        'vehicle_type',
        'license_number',
        'experience_years',
        'is_available',
        'current_latitude',
        'current_longitude',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'experience_years' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function foodOrders()
    {
        return $this->hasMany(FoodOrder::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)->where('status', self::STATUS_ACTIVE);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
