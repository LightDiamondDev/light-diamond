<?php

namespace App\Services\MaterialFile;

use App\Models\MaterialFileLocalization;
use App\Models\MaterialFileState;
use App\Services\MaterialFile\Dto\MaterialFileLocalizationDto;

class MaterialFileLocalizationService
{
    public function create(MaterialFileState $fileState, MaterialFileLocalizationDto $dto): MaterialFileLocalization
    {
        $localization = MaterialFileLocalization::make();
        $localization->fileState()->associate($fileState);
        $localization->language = $dto->language;
        $localization->name     = $dto->name;
        $localization->save();

        return $localization;
    }

    public function update(MaterialFileLocalization $localization, MaterialFileLocalizationDto $dto): void
    {
        if ($dto->language !== null) {
            $localization->language = $dto->language;
        }
        if ($dto->name !== null) {
            $localization->name = $dto->name;
        }
        $localization->save();
    }

    public function delete(MaterialFileLocalization $localization): void
    {
        $localization->delete();
    }
}
