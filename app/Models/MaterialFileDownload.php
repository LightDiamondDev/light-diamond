<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\MaterialFileDownload
 *
 * @property int $id
 * @property int $file_id
 * @property string $ip
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MaterialFile $file
 * @method static Builder|MaterialFileDownload newModelQuery()
 * @method static Builder|MaterialFileDownload newQuery()
 * @method static Builder|MaterialFileDownload ofMaterial(int $materialId)
 * @method static Builder|MaterialFileDownload query()
 * @method static Builder|MaterialFileDownload whereCreatedAt($value)
 * @method static Builder|MaterialFileDownload whereFileId($value)
 * @method static Builder|MaterialFileDownload whereId($value)
 * @method static Builder|MaterialFileDownload whereIp($value)
 * @method static Builder|MaterialFileDownload whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MaterialFileDownload extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(MaterialFile::class, 'file_id');
    }

    public function scopeOfMaterial(Builder $query, int $materialId): Builder|MaterialFileDownload
    {
        return $query
            ->join('material_files', 'material_files.id', '=', 'material_file_downloads.file_id')
            ->join('material_versions', 'material_versions.id', '=', 'material_files.version_id')
            ->where('material_versions.material_id', $materialId)
            ->select('material_file_downloads.*')
            ->distinct('material_file_downloads.ip');
    }
}
