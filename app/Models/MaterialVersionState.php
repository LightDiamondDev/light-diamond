<?php

namespace App\Models;

use App\Models\Traits\Publishable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\MaterialVersionState
 *
 * @property int $id
 * @property int $version_id
 * @property string $number
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MaterialVersionLocalization|null $localization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialVersionLocalization> $localizations
 * @property-read int|null $localizations_count
 * @property-read \App\Models\MaterialVersion $version
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionState newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionState newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionState query()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionState whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionState whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionState whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionState wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionState whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionState whereVersionId($value)
 * @mixin \Eloquent
 */
class MaterialVersionState extends Model
{
    use HasFactory, Publishable;

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $fillable = [
        'number',
    ];

    protected $with = [
        'localizations',
    ];

    protected $appends = [
        'localization',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(MaterialVersion::class, 'version_id')->withoutGlobalScopes();
    }

    public function localizations(): HasMany
    {
        return $this->hasMany(MaterialVersionLocalization::class, 'version_state_id');
    }

    public function getLocalizationAttribute(): MaterialVersionLocalization|null
    {
        /** @type MaterialVersionLocalization|null */
        return $this->localizations()->first();
    }
}
