<?php

namespace App\Models;

use App\Models\Enums\GameEdition;
use App\Models\Enums\MaterialSubmissionStatus;
use App\Models\Traits\Publishable;
use App\Registries\CategoryType;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Material
 *
 * @property int $id
 * @property string $slug
 * @property CategoryType $category
 * @property GameEdition|null $edition
 * @property int $views_count
 * @property int $downloads_count
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialComment> $comments
 * @property-read int|null $comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FavouriteMaterial> $favourites
 * @property-read int|null $favourites_count
 * @property-read mixed $active_submission_id
 * @property-read bool $is_favourite
 * @property-read bool $is_liked
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialLike> $likes
 * @property-read int|null $likes_count
 * @property-read \App\Models\MaterialState|null $state
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialState> $states
 * @property-read int|null $states_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialSubmission> $submissions
 * @property-read int|null $submissions_count
 * @property-read \App\Models\MaterialVersion|null $version
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialVersion> $versions
 * @property-read int|null $versions_count
 * @method static Builder|Material favouriteOfUser(int $userId)
 * @method static Builder|Material newModelQuery()
 * @method static Builder|Material newQuery()
 * @method static Builder|Material ofUser(int $userId)
 * @method static Builder|Material onlyTrashed()
 * @method static Builder|Material query()
 * @method static Builder|Material whereCategory($value)
 * @method static Builder|Material whereCreatedAt($value)
 * @method static Builder|Material whereDeletedAt($value)
 * @method static Builder|Material whereDownloadsCount($value)
 * @method static Builder|Material whereEdition($value)
 * @method static Builder|Material whereId($value)
 * @method static Builder|Material wherePublishedAt($value)
 * @method static Builder|Material whereSlug($value)
 * @method static Builder|Material whereUpdatedAt($value)
 * @method static Builder|Material whereViewsCount($value)
 * @method static Builder|Material withTrashed()
 * @method static Builder|Material withoutTrashed()
 * @mixin \Eloquent
 */
class Material extends Model
{
    use HasFactory, SoftDeletes, Publishable;

    protected $fillable = [
        'slug',
        'category',
        'edition',
    ];

    protected $casts = [
        'category'     => CategoryType::class,
        'edition'      => GameEdition::class,
        'published_at' => 'datetime',
    ];

    protected $with = [
        'state',
        'version',
    ];

    protected $withCount = [
        'likes',
        'favourites',
        'comments',
    ];

    protected $appends = [
        'is_liked',
        'is_favourite',
        'active_submission_id'
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (self $version) {
            if (!$version->isForceDeleting()) {
                $version->versions()->delete();
            }
        });
    }

    public function states(): HasMany
    {
        return $this->hasMany(MaterialState::class, 'material_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(MaterialVersion::class, 'material_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(MaterialSubmission::class, 'material_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(MaterialLike::class, 'material_id');
    }

    public function favourites(): HasMany
    {
        return $this->hasMany(FavouriteMaterial::class, 'material_id');
    }

    public function comments(): HasManyThrough
    {
        return $this->hasManyThrough(
            MaterialComment::class,
            MaterialVersion::class,
            'material_id',
            'version_id'
        );
    }

    public function state(): HasOne
    {
        return $this
            ->hasOne(MaterialState::class, 'material_id')
            ->latestOfMany('published_at');
    }

    public function version(): HasOne
    {
        return $this
            ->hasOne(MaterialVersion::class, 'material_id')
            ->latestOfMany('published_at');
    }

    public function scopeOfUser(Builder $query, int $userId): Builder|Material
    {
        return $query->whereHas('state', function ($query) use ($userId) {
            $query->where('author_id', $userId);
        });
    }

    public function scopeFavouriteOfUser(Builder $query, int $userId): Builder|Material
    {
        return $query->whereHas('favourites', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        });
    }

    public function getIsLikedAttribute(): bool
    {
        $user = Auth::user();
        return $user !== null && $this->likes()->where('user_id', $user->id)->exists();
    }

    public function getIsFavouriteAttribute(): bool
    {
        $user = Auth::user();
        return $user !== null && $this->favourites()->where('user_id', $user->id)->exists();
    }

    public function getActiveSubmissionIdAttribute()
    {
        return MaterialSubmission
            ::where('material_id', $this->id)
            ->whereIn('status', [MaterialSubmissionStatus::Draft, MaterialSubmissionStatus::Pending])
            ->withoutGlobalScopes()
            ->pluck('id')
            ->first();
    }
}
