<?php

namespace App\Models;

use App\Models\Traits\Publishable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\MaterialVersion
 *
 * @property int $id
 * @property int $material_id
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialComment> $comments
 * @property-read int|null $comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialFile> $files
 * @property-read int|null $files_count
 * @property-read \App\Models\Material $material
 * @property-read \App\Models\MaterialVersionState|null $state
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialVersionState> $states
 * @property-read int|null $states_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialVersionSubmission> $submissions
 * @property-read int|null $submissions_count
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersion onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersion query()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersion whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersion whereMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersion wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersion withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersion withoutTrashed()
 * @mixin \Eloquent
 */
class MaterialVersion extends Model
{
    use HasFactory, SoftDeletes, Publishable;

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $with = [
        'state',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (self $version) {
            if (!$version->isForceDeleting()) {
                $version->comments()->delete();
                $version->files()->delete();
            }
        });
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id')->withoutGlobalScopes();
    }

    public function states(): HasMany
    {
        return $this->hasMany(MaterialVersionState::class, 'version_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(MaterialFile::class, 'version_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(MaterialComment::class, 'version_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(MaterialVersionSubmission::class, 'version_id');
    }

    public function state(): HasOne
    {
        return $this
            ->hasOne(MaterialVersionState::class, 'version_id')
            ->latestOfMany('published_at');
    }
}
