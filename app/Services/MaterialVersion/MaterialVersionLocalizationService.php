<?php

namespace App\Services\MaterialVersion;

use App\Models\MaterialVersionLocalization;
use App\Models\MaterialVersionState;
use App\Services\MaterialVersion\Dto\MaterialVersionLocalizationDto;

class MaterialVersionLocalizationService
{
    public function create(MaterialVersionState $versionState, MaterialVersionLocalizationDto $dto): MaterialVersionLocalization
    {
        $localization = MaterialVersionLocalization::make();
        $localization->versionState()->associate($versionState);
        $localization->language  = $dto->language;
        $localization->name      = $dto->name;
        $localization->changelog = $dto->changelog;
        $localization->save();

        return $localization;
    }

    public function update(MaterialVersionLocalization $localization, MaterialVersionLocalizationDto $dto): void
    {
        if ($dto->language !== null) {
            $localization->language = $dto->language;
        }
        if ($dto->name !== null) {
            $localization->name = $dto->name;
        }
        $localization->save();
    }

    public function delete(MaterialVersionLocalization $localization): void
    {
        $localization->delete();
    }
}
