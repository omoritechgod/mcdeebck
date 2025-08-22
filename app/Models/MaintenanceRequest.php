<?php

// app/Models/MaintenanceRequest.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $location
 * @property string $service_type
 * @property string $issue
 * @property int $needs_towing
 * @property string $status
 * @property int|null $mechanic_id
 * @property string|null $accepted_at
 * @property string|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $mechanic
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereIssue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereMechanicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereNeedsTowing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereServiceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereUserId($value)
 * @mixin \Eloquent
 */
class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'location',
        'service_type',
        'issue',
        'needs_towing',
        'status',
        'mechanic_id',
        'accepted_at',
        'completed_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mechanic()
    {
        return $this->belongsTo(User::class, 'mechanic_id');
    }
}
