<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\FavouritePost
 *
 * @property int $id
 * @property int $user_id
 * @property int $post_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Post $post
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|FavouritePost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FavouritePost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FavouritePost query()
 * @method static \Illuminate\Database\Eloquent\Builder|FavouritePost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FavouritePost whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FavouritePost wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FavouritePost whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FavouritePost whereUserId($value)
 * @mixin \Eloquent
 */
class FavouritePost extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
