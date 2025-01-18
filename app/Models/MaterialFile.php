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
 * App\Models\MaterialFile
 *
 * @property int $id
 * @property int $version_id
 * @property string|null $path
 * @property string|null $url
 * @property int|null $size
 * @property string|null $extension
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MaterialFileState|null $state
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialFileState> $states
 * @property-read int|null $states_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialFileSubmission> $submissions
 * @property-read int|null $submissions_count
 * @property-read \App\Models\MaterialVersion $version
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFile onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFile whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFile whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFile wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFile wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFile whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFile whereVersionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFile withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFile withoutTrashed()
 * @mixin \Eloquent
 */
class MaterialFile extends Model
{
    use HasFactory, SoftDeletes, Publishable;

    protected $fillable = [
        'path',
        'url',
        'size',
        'extension',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $with = [
        'state',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(MaterialVersion::class, 'version_id')->withoutGlobalScopes();
    }

    public function states(): HasMany
    {
        return $this->hasMany(MaterialFileState::class, 'file_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(MaterialFileSubmission::class, 'file_id');
    }

    public function state(): HasOne
    {
        return $this
            ->hasOne(MaterialFileState::class, 'file_id')
            ->latestOfMany('published_at');
    }
}
