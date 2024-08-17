<?php

namespace App\Models;

use App\Models\Enums\GameEdition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PostCategory
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property GameEdition|null $edition
 * @property bool $is_article
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PostCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PostCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PostCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|PostCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostCategory whereEdition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostCategory whereIsArticle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostCategory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PostCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'edition',
        'is_article',
    ];

    protected $casts = [
        'edition' => GameEdition::class,
        'is_article' => 'boolean',
    ];

    protected $attributes = [
        'edition' => null,
        'is_article' => false,
    ];
}
