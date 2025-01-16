<?php

namespace App\Models;

use App\Models\Enums\MaterialSubmissionActionType;
use App\Models\Enums\MaterialSubmissionStatus;
use App\Models\Enums\SubmissionType;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\MaterialSubmission
 *
 * @property int $id
 * @property int $material_id
 * @property int|null $material_state_id
 * @property int|null $submitter_id
 * @property int|null $assigned_moderator_id
 * @property SubmissionType $type
 * @property MaterialSubmissionStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Collection $actions
 * @property-read int|null $actions_count
 * @property-read \App\Models\User|null $assignedModerator
 * @property-read \App\Models\Material $material
 * @property-read \App\Models\MaterialState|null $materialState
 * @property-read \App\Models\User|null $submitter
 * @property-read Collection<int, \App\Models\MaterialVersionSubmission> $versionSubmissions
 * @property-read int|null $version_submissions_count
 * @method static Builder|MaterialSubmission newModelQuery()
 * @method static Builder|MaterialSubmission newQuery()
 * @method static Builder|MaterialSubmission query()
 * @method static Builder|MaterialSubmission whereAssignedModeratorId($value)
 * @method static Builder|MaterialSubmission whereCreatedAt($value)
 * @method static Builder|MaterialSubmission whereId($value)
 * @method static Builder|MaterialSubmission whereMaterialId($value)
 * @method static Builder|MaterialSubmission whereMaterialStateId($value)
 * @method static Builder|MaterialSubmission whereStatus($value)
 * @method static Builder|MaterialSubmission whereSubmitterId($value)
 * @method static Builder|MaterialSubmission whereType($value)
 * @method static Builder|MaterialSubmission whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MaterialSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'status',
    ];

    protected $hidden = [
        'assigned_moderator_id',
    ];

    protected $casts = [
        'type'   => SubmissionType::class,
        'status' => MaterialSubmissionStatus::class,
    ];

    protected $attributes = [
        'status' => MaterialSubmissionStatus::Draft,
    ];

    protected $with = [
        'materialState',
        'submitter',
        'actions',
    ];

    protected $appends = [
        'actions',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('withModeratorAndLimitedActions', function (Builder $builder) {
            if (Auth::user()?->is_moderator) {
                $builder->with('assignedModerator');
            } else {
                $builder->with([
                    'actions' => function (HasMany $builder) {
                        $builder->whereNot('type', MaterialSubmissionActionType::AssignModerator);
                    }
                ]);
            }
        });

        static::retrieved(function (MaterialSubmission $submission) {
            if (Auth::user()?->is_moderator) {
                $submission->makeVisible('assigned_moderator_id');
            }
        });
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id')->withoutGlobalScopes();
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitter_id');
    }

    public function materialState(): BelongsTo
    {
        return $this->belongsTo(MaterialState::class, 'material_state_id')->withoutGlobalScopes();
    }

    public function assignedModerator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_moderator_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(MaterialSubmissionAction::class, 'submission_id');
    }

    public function versionSubmissions(): HasMany
    {
        return $this->hasMany(MaterialVersionSubmission::class, 'material_submission_id');
    }

    public function getActionsAttribute(): Collection
    {
        $actions     = $this->getRelationValue('actions');
        $isModerator = Auth::user()->is_moderator;

        $actions->each(function (MaterialSubmissionAction $action) use ($isModerator) {
            if ($isModerator) {
                if ($action->type === MaterialSubmissionActionType::AssignModerator) {
                    $details              = $action->details;
                    $details['moderator'] = User::find($details['moderator_id']);
                    $action->details      = $details;
                }
            } else {
                if ($action->type !== MaterialSubmissionActionType::Submit) {
                    $action->makeHidden('user_id');
                    $action->makeHidden('user');
                }
            }
        });

        return $actions;
    }
}
