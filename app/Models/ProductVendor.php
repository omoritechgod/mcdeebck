<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $vendor_id
 * @property string $contact_person
 * @property string $store_address
 * @property string $store_phone
 * @property string|null $store_email
 * @property string|null $store_description
 * @property string|null $logo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Vendor $vendor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVendor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVendor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVendor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVendor whereContactPerson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVendor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVendor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVendor whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVendor whereStoreAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVendor whereStoreDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVendor whereStoreEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVendor whereStorePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVendor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVendor whereVendorId($value)
 * @mixin \Eloquent
 */
class ProductVendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'contact_person',
        'store_address',
        'store_phone',
        'store_email',
        'store_description',
        'logo',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
