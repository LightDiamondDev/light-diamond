<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * App\Models\MaterialComment
 *
 * @property int $id
 * @property int $version_id
 * @property int|null $user_id
 * @property int|null $parent_comment_id
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MaterialComment> $childComments
 * @property-read int|null $child_comments_count
 * @property-read bool $is_liked
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialCommentLike> $likes
 * @property-read int|null $likes_count
 * @property-read MaterialComment|null $parentComment
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\MaterialVersion $version
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialComment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialComment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialComment query()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialComment whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialComment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialComment whereParentCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialComment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialComment whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialComment whereVersionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialComment withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialComment withoutTrashed()
 * @mixin \Eloquent
 */
class MaterialComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'content',
    ];

    protected $with = [
        'user',
        'parentComment',
    ];

    protected $withCount = [
        'likes',
    ];

    protected $appends = [
        'is_liked',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (self $version) {
            if (!$version->isForceDeleting()) {
                // Пока что помечаем все ответные комментарии удалёнными, когда данный
                // помечается таковым, чтобы они не загружались. Если этого не делать,
                // ответные комментарии отображаются как самостоятельные на фронте.
                // В дальнейшем, думаю, нужно будет сделать, чтобы загружались
                // даже удалённые комментарии, но без автора и содержимого.
                $version->childComments()->delete();
            }
        });
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(MaterialVersion::class, 'version_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parentComment(): BelongsTo
    {
        return $this->belongsTo(MaterialComment::class, 'parent_comment_id');
    }

    public function childComments(): HasMany
    {
        return $this->hasMany(MaterialComment::class, 'parent_comment_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(MaterialCommentLike::class, 'comment_id');
    }

    public function getIsLikedAttribute(): bool
    {
        $user = Auth::user();
        return $user !== null && $this->likes()->where('user_id', $user->id)->exists();
    }
}
