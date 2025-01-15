<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Storage;

/**
 * App\Models\MaterialLocalization
 *
 * @property int $id
 * @property int $material_state_id
 * @property string $language
 * @property string $cover
 * @property string $title
 * @property string $description
 * @property string $content
 * @property-read string $cover_url
 * @property-read \App\Models\MaterialState $materialState
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialLocalization newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialLocalization newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialLocalization query()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialLocalization whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialLocalization whereCover($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialLocalization whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialLocalization whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialLocalization whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialLocalization whereMaterialStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialLocalization whereTitle($value)
 * @mixin \Eloquent
 */
class MaterialLocalization extends Model
{
    use HasFactory;

    protected $fillable = [
        'language',
        'cover',
        'title',
        'description',
        'content',
    ];

    protected $appends = [
        'cover_url',
    ];

    public $timestamps = false;

    public function materialState(): BelongsTo
    {
        return $this->belongsTo(MaterialState::class, 'material_state_id')->withoutGlobalScopes();
    }

    public function getCoverUrlAttribute(): string
    {
        return url(Storage::url($this->cover));
    }
}
