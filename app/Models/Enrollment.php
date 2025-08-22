<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Enrollment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Enrollment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Enrollment query()
 * @mixin \Eloquent
 */
class Enrollment extends Model
{
    protected $fillable = [
        'full_name', 
        'course', 
        'email', 
        'phone', 
        'terms'
    ];
}
