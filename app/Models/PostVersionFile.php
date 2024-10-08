<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\PostVersionFile
 *
 * @property int $id
 * @property int $post_version_id
 * @property string $name
 * @property string|null $path
 * @property string|null $url
 * @property int|null $size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PostVersion $postVersion
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionFile whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionFile wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionFile wherePostVersionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionFile whereUrl($value)
 * @mixin \Eloquent
 */
class PostVersionFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'path',
        'url',
        'size',
    ];

    public function postVersion(): BelongsTo
    {
        return $this->belongsTo(PostVersion::class, 'post_version_id');
    }
}
