<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\MaterialFileLocalization
 *
 * @property int $id
 * @property int $file_state_id
 * @property string $language
 * @property string $name
 * @property-read \App\Models\MaterialFileState $fileState
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileLocalization newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileLocalization newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileLocalization query()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileLocalization whereFileStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileLocalization whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileLocalization whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileLocalization whereName($value)
 * @mixin \Eloquent
 */
class MaterialFileLocalization extends Model
{
    use HasFactory;

    protected $fillable = [
        'language',
        'name',
    ];

    public $timestamps = false;

    public function fileState(): BelongsTo
    {
        return $this->belongsTo(MaterialFileState::class, 'file_state_id')->withoutGlobalScopes();
    }
}
