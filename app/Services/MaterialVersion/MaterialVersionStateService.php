<?php

namespace App\Services\MaterialVersion;

use App\Models\MaterialVersion;
use App\Models\MaterialVersionState;
use App\Services\MaterialVersion\Dto\MaterialVersionStateDto;

readonly class MaterialVersionStateService
{
    public function __construct(private MaterialVersionLocalizationService $localizationService)
    {
    }

    public function create(MaterialVersion $version, MaterialVersionStateDto $dto): MaterialVersionState
    {
        $state = MaterialVersionState::make();
        $state->version()->associate($version);
        $state->number = $dto->number;
        $state->save();

        foreach ($dto->localizations as $localizationDto) {
            $this->localizationService->create($state, $localizationDto);
        }

        return $state;
    }

    public function update(MaterialVersionState $state, MaterialVersionStateDto $dto): void
    {
        if ($dto->number !== null) {
            $state->number = $dto->number;
        }
        if ($dto->localizations !== null) {
            $localizationIds = collect($dto->localizations)
                ->pluck('id')
                ->filter()
                ->all();

            $state->localizations
                ->reject(fn($localization) => in_array($localization->id, $localizationIds))
                ->each(fn($oldLocalization) => $this->localizationService->delete($oldLocalization));

            foreach ($dto->localizations as $localizationDto) {
                if ($localizationDto->id === null) {
                    $this->localizationService->create($state, $localizationDto);
                } else {
                    $localization = $state->localizations->firstWhere('id', $localizationDto->id);
                    if ($localization) {
                        $this->localizationService->update($localization, $localizationDto);
                    }
                }
            }
        }

        $state->save();
    }

    public function delete(MaterialVersionState $state): void
    {
        $state->delete();
    }
}
