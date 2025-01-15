<?php

namespace App\Models;

use App\Models\Enums\MaterialSubmissionActionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\MaterialSubmissionAction
 *
 * @property int $id
 * @property int $submission_id
 * @property int|null $user_id
 * @property MaterialSubmissionActionType $type
 * @property array $details
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MaterialSubmission $submission
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialSubmissionAction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialSubmissionAction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialSubmissionAction query()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialSubmissionAction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialSubmissionAction whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialSubmissionAction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialSubmissionAction whereSubmissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialSubmissionAction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialSubmissionAction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialSubmissionAction whereUserId($value)
 * @mixin \Eloquent
 */
class MaterialSubmissionAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'details',
    ];

    protected $casts = [
        'type'    => MaterialSubmissionActionType::class,
        'details' => 'array',
    ];

    protected $attributes = [
        'details' => '{}',
    ];

    protected $with = [
        'user',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(MaterialSubmission::class, 'submission_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
