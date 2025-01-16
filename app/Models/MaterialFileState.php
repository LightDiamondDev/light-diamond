<?php

namespace App\Models;

use App\Models\Traits\Publishable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\MaterialFileState
 *
 * @property int $id
 * @property int $file_id
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MaterialFile $file
 * @property-read \App\Models\MaterialFileLocalization|null $localization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialFileLocalization> $localizations
 * @property-read int|null $localizations_count
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileState newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileState newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileState query()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileState whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileState whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileState whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileState wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileState whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MaterialFileState extends Model
{
    use HasFactory, Publishable;

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $with = [
        'localizations',
    ];

    protected $appends = [
        'localization',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(MaterialFile::class, 'file_id')->withoutGlobalScopes();
    }

    public function localizations(): HasMany
    {
        return $this->hasMany(MaterialFileLocalization::class, 'file_state_id');
    }

    public function getLocalizationAttribute(): MaterialFileLocalization|null
    {
        /** @type MaterialFileLocalization|null */
        return $this->localizations()->first();
    }
}
