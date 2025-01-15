<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\MaterialCommentLike
 *
 * @property int $id
 * @property int $comment_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MaterialComment $comment
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialCommentLike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialCommentLike newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialCommentLike query()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialCommentLike whereCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialCommentLike whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialCommentLike whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialCommentLike whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialCommentLike whereUserId($value)
 * @mixin \Eloquent
 */
class MaterialCommentLike extends Model
{
    use HasFactory;

    public function comment(): BelongsTo
    {
        return $this->belongsTo(MaterialComment::class, 'comment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
