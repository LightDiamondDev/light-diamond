<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\MaterialView
 *
 * @property int $id
 * @property int $version_id
 * @property string $ip
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Material $version
 * @method static Builder|MaterialView newModelQuery()
 * @method static Builder|MaterialView newQuery()
 * @method static Builder|MaterialView ofMaterial(int $materialId)
 * @method static Builder|MaterialView query()
 * @method static Builder|MaterialView whereCreatedAt($value)
 * @method static Builder|MaterialView whereId($value)
 * @method static Builder|MaterialView whereIp($value)
 * @method static Builder|MaterialView whereUpdatedAt($value)
 * @method static Builder|MaterialView whereVersionId($value)
 * @mixin \Eloquent
 */
class MaterialView extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'version_id');
    }

    public function scopeOfMaterial(Builder $query, int $materialId): Builder|MaterialView
    {
        return $query
            ->join('material_versions', 'material_versions.id', '=', 'material_views.version_id')
            ->where('material_versions.material_id', $materialId)
            ->select('material_views.*')
            ->distinct('material_views.ip');
    }
}
