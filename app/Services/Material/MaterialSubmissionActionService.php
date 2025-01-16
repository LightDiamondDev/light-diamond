<?php

namespace App\Services\Material;

use App\Models\MaterialSubmission;
use App\Models\MaterialSubmissionAction;
use App\Services\Material\Dto\MaterialSubmissionActionDto;
use Auth;

class MaterialSubmissionActionService
{
    public function create(MaterialSubmission $submission, MaterialSubmissionActionDto $dto): MaterialSubmissionAction
    {
        $action = MaterialSubmissionAction::make();
        $action->submission()->associate($submission);
        $action->user()->associate(Auth::user());
        $action->type    = $dto->type;
        $action->details = $dto->details;
        $action->save();

        return $action;
    }
}
