<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property int $rating
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereUserId($value)
 * @mixin \Eloquent
 */
class ProductReview extends Model
{
    protected $fillable = ['user_id', 'product_id', 'rating', 'comment'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
