<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\FavouriteMaterial
 *
 * @property int $id
 * @property int $material_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Material $material
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|FavouriteMaterial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FavouriteMaterial newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FavouriteMaterial query()
 * @method static \Illuminate\Database\Eloquent\Builder|FavouriteMaterial whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FavouriteMaterial whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FavouriteMaterial whereMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FavouriteMaterial whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FavouriteMaterial whereUserId($value)
 * @mixin \Eloquent
 */
class FavouriteMaterial extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
