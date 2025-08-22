<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $vendor_id
 * @property string $business_name
 * @property string $specialty
 * @property string $location
 * @property string $contact_phone
 * @property string|null $contact_email
 * @property string|null $description
 * @property string|null $logo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Vendor $vendor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereBusinessName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereContactEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereSpecialty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FoodVendor whereVendorId($value)
 * @mixin \Eloquent
 */
class FoodVendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'business_name',
        'specialty',
        'location',
        'contact_phone',
        'contact_email',
        'description',
        'logo'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
