<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $vendor_id
 * @property string $full_name
 * @property string $phone_number
 * @property string|null $organization_name
 * @property string|null $organization_address
 * @property string|null $website
 * @property string|null $years_of_experience
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Vendor $vendor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceApartment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceApartment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceApartment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceApartment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceApartment whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceApartment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceApartment whereOrganizationAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceApartment whereOrganizationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceApartment wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceApartment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceApartment whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceApartment whereWebsite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceApartment whereYearsOfExperience($value)
 * @mixin \Eloquent
 */
class ServiceApartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'full_name',
        'phone_number',
        'organization_name',
        'organization_address',
        'website',
        'years_of_experience',
    ];

    /**
     * Get the vendor that owns this service apartment profile.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
