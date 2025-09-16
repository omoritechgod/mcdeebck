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

    protected $fillable = [
        'user_id',
        'vendor_id',
        'status',          // e.g., 'active', 'pending', 'suspended'
        'vehicle_type',    // e.g., bike, tricycle, car
        'license_number',
        'experience_years',
        'availability',
        'current_lat',
        'current_lng'
    ];

    // Relationships

    /**
     * Link to the user account.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Link to the vendor entry for tracking category and verification.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function rides(){
        return $this->hasMany(Ride::class, 'rider_id', 'id');
    }
}
