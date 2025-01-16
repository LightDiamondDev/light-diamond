<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\MaterialLike
 *
 * @property int $id
 * @property int $material_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Material $material
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialLike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialLike newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialLike query()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialLike whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialLike whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialLike whereMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialLike whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialLike whereUserId($value)
 * @mixin \Eloquent
 */
class MaterialLike extends Model
{
    use HasFactory;

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
