<?php

namespace App\Models;

use App\Models\Enums\MaterialSubmissionStatus;
use App\Models\Enums\SubmissionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\MaterialVersionSubmission
 *
 * @property int $id
 * @property int $version_id
 * @property int $material_submission_id
 * @property int|null $version_state_id
 * @property SubmissionType $type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialFileSubmission> $fileSubmissions
 * @property-read int|null $file_submissions_count
 * @property-read bool $is_closed
 * @property-read MaterialSubmissionStatus $status
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MaterialSubmission $materialSubmission
 * @property-read \App\Models\MaterialVersion $version
 * @property-read \App\Models\MaterialVersionState|null $versionState
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionSubmission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionSubmission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionSubmission query()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionSubmission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionSubmission whereMaterialSubmissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionSubmission whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionSubmission whereVersionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialVersionSubmission whereVersionStateId($value)
 * @mixin \Eloquent
 */
class MaterialVersionSubmission extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'type',
    ];

    protected $casts = [
        'type' => SubmissionType::class,
    ];

    protected $with = [
        'fileSubmissions',
        'versionState',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(MaterialVersion::class, 'version_id')->withoutGlobalScopes();
    }

    public function materialSubmission(): BelongsTo
    {
        return $this->belongsTo(MaterialSubmission::class, 'material_submission_id');
    }

    public function versionState(): BelongsTo
    {
        return $this->belongsTo(MaterialVersionState::class, 'version_state_id')->withoutGlobalScopes();
    }

    public function fileSubmissions(): HasMany
    {
        return $this->hasMany(MaterialFileSubmission::class, 'version_submission_id');
    }

    public function getUpdatedAtAttribute(): Carbon|null
    {
        return $this->materialSubmission->updated_at;
    }

    public function getStatusAttribute(): MaterialSubmissionStatus
    {
        return $this->materialSubmission->status;
    }

    public function getIsClosedAttribute(): bool
    {
        return $this->materialSubmission->is_closed;
    }
}
