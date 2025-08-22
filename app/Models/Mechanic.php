<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $vendor_id
 * @property string|null $workshop_name
 * @property string|null $services_offered
 * @property string|null $location
 * @property string|null $contact_number
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Vendor $vendor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mechanic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mechanic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mechanic query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mechanic whereContactNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mechanic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mechanic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mechanic whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mechanic whereServicesOffered($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mechanic whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mechanic whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mechanic whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mechanic whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mechanic whereWorkshopName($value)
 * @mixin \Eloquent
 */
class Mechanic extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vendor_id',
        'workshop_name',
        'services_offered',
        'location',
        'contact_number',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
