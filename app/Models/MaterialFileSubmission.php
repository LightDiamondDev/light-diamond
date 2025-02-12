<?php

namespace App\Models;

use App\Models\Enums\MaterialSubmissionStatus;
use App\Models\Enums\SubmissionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\MaterialFileSubmission
 *
 * @property int $id
 * @property int $file_id
 * @property int $version_submission_id
 * @property int|null $file_state_id
 * @property SubmissionType $type
 * @property-read \App\Models\MaterialFile $file
 * @property-read \App\Models\MaterialFileState|null $fileState
 * @property-read bool $is_closed
 * @property-read MaterialSubmissionStatus $status
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MaterialVersionSubmission $versionSubmission
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileSubmission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileSubmission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileSubmission query()
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileSubmission whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileSubmission whereFileStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileSubmission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileSubmission whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaterialFileSubmission whereVersionSubmissionId($value)
 * @mixin \Eloquent
 */
class MaterialFileSubmission extends Model
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
        'file',
        'fileState',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(MaterialFile::class, 'file_id')->withoutGlobalScopes();
    }

    public function versionSubmission(): BelongsTo
    {
        return $this->belongsTo(MaterialVersionSubmission::class, 'version_submission_id');
    }

    public function fileState(): BelongsTo
    {
        return $this->belongsTo(MaterialFileState::class, 'file_state_id')->withoutGlobalScopes();
    }

    public function getUpdatedAtAttribute(): Carbon|null
    {
        return $this->versionSubmission->updated_at;
    }

    public function getStatusAttribute(): MaterialSubmissionStatus
    {
        return $this->versionSubmission->status;
    }

    public function getIsClosedAttribute(): bool
    {
        return $this->versionSubmission->is_closed;
    }
}
