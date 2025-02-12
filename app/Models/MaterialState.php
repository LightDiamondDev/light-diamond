<?php

namespace App\Models;

use App\Models\Traits\Publishable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\MaterialState
 *
 * @property int $id
 * @property int $material_id
 * @property int|null $author_id
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $author
 * @property-read \App\Models\MaterialLocalization|null $localization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialLocalization> $localizations
 * @property-read int|null $localizations_count
 * @property-read \App\Models\Material $material
 * @property-read \App\Models\MaterialSubmission|null $materialSubmission
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialState newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialState newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialState query()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialState whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialState whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialState whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialState whereMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialState wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialState whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MaterialState extends Model
{
    use HasFactory, Publishable;

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $with = [
        'author',
        'localizations',
    ];

    protected $appends = [
        'localization',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id')->withoutGlobalScopes();
    }

    public function materialSubmission(): HasOne
    {
        return $this->hasOne(MaterialSubmission::class, 'material_state_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function localizations(): HasMany
    {
        return $this->hasMany(MaterialLocalization::class, 'material_state_id');
    }

    public function getLocalizationAttribute(): MaterialLocalization|null
    {
        /** @type MaterialLocalization|null */
        return $this->localizations()->first();
    }
}
