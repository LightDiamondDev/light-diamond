<?php

namespace App\Models;

use App\Models\Enums\UserRole;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string|null $first_name
 * @property string|null $last_name
 * @property UserRole $role
 * @property mixed $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PostComment> $comments
 * @property-read int|null $comments_count
 * @property-read int $comment_count
 * @property-read int $favourite_post_count
 * @property-read bool $is_admin
 * @property-read bool $is_moderator
 * @property-read int $post_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereFirstName($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLastName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereRole($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereUsername($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
    ];

    protected $attributes = [
        'role' => UserRole::User,
    ];

    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class, 'user_id');
    }

    public function getPostCountAttribute(): int
    {
        return Post::ofUser($this->id)->count();
    }

    public function getFavouritePostCountAttribute(): int
    {
        return Post::favouriteOfUser($this->id)->count();
    }

    public function getCommentCountAttribute(): int
    {
        return $this->comments()->count();
    }

    public function getCollectedLikeCountAttribute(): int
    {
        return Post::ofUser($this->id)
            ->join('post_likes', 'posts.id', '=', 'post_likes.post_id')
            ->count('post_likes.id');
    }

    public function getCollectedDownloadCountAttribute(): int
    {
        return Post::ofUser($this->id)->sum('posts.download_count');
    }

    public function getCollectedViewCountAttribute(): int
    {
        return Post::ofUser($this->id)
            ->join('post_views', 'posts.id', '=', 'post_views.post_id')
            ->count('post_views.id');
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function getIsModeratorAttribute(): bool
    {
        return in_array($this->role, [UserRole::Moderator, UserRole::Admin]);
    }
}
