<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\PostVersionFileDownload
 *
 * @property int $id
 * @property int $file_id
 * @property string $ip
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PostVersionFile $file
 * @method static Builder|PostVersionFileDownload newModelQuery()
 * @method static Builder|PostVersionFileDownload newQuery()
 * @method static Builder|PostVersionFileDownload ofPost(int $postId)
 * @method static Builder|PostVersionFileDownload query()
 * @method static Builder|PostVersionFileDownload whereCreatedAt($value)
 * @method static Builder|PostVersionFileDownload whereFileId($value)
 * @method static Builder|PostVersionFileDownload whereId($value)
 * @method static Builder|PostVersionFileDownload whereIp($value)
 * @method static Builder|PostVersionFileDownload whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PostVersionFileDownload extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(PostVersionFile::class, 'file_id');
    }

    public function scopeOfPost(Builder $query, int $postId): Builder|PostVersionFileDownload
    {
        return $query
            ->join('post_version_files', 'post_version_files.id', '=', 'post_version_file_downloads.file_id')
            ->join('post_versions', 'post_versions.id', '=', 'post_version_files.post_version_id')
            ->where('post_versions.post_id', $postId)
            ->select('post_version_file_downloads.*')->distinct('post_version_file_downloads.ip');
    }
}
