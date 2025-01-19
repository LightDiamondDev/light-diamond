<?php

namespace App\Services\Material;

use App\Models\MaterialLocalization;
use App\Models\MaterialState;
use App\Services\Material\Dto\MaterialLocalizationDto;

class MaterialLocalizationService
{
    public function create(MaterialState $materialState, MaterialLocalizationDto $dto): MaterialLocalization
    {
        $localization = MaterialLocalization::make();
        $localization->materialState()->associate($materialState);
        $localization->language    = $dto->language;
        $localization->cover       = $dto->cover;
        $localization->title       = $dto->title;
        $localization->description = $dto->description;
        $localization->content     = clean($dto->content, config('purifier.material'));
        $localization->save();

        return $localization;
    }

    public function update(MaterialLocalization $localization, MaterialLocalizationDto $dto): void
    {
        if ($dto->language !== null) {
            $localization->language = $dto->language;
        }
        if ($dto->cover !== null) {
            $localization->cover = $dto->cover;
        }
        if ($dto->title !== null) {
            $localization->title = $dto->title;
        }
        if ($dto->description !== null) {
            $localization->description = $dto->description;
        }
        if ($dto->content !== null && $dto->content !== $localization->content) {
            $localization->content = clean($dto->content, config('purifier.material'));
        }
        $localization->save();
    }

    public function delete(MaterialLocalization $localization): void
    {
        $localization->delete();
    }
}
