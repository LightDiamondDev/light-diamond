<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\MaterialVersionLocalization
 *
 * @property int $id
 * @property int $version_state_id
 * @property string $language
 * @property string|null $name
 * @property string|null $changelog
 * @property-read \App\Models\MaterialVersionState $versionState
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionLocalization newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionLocalization newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionLocalization query()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionLocalization whereChangelog($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionLocalization whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionLocalization whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionLocalization whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionLocalization whereVersionStateId($value)
 * @mixin \Eloquent
 */
class MaterialVersionLocalization extends Model
{
    use HasFactory;

    protected $fillable = [
        'language',
        'name',
        'changelog',
    ];

    public $timestamps = false;

    public function versionState(): BelongsTo
    {
        return $this->belongsTo(MaterialVersionState::class, 'version_state_id')->withoutGlobalScopes();
    }
}
