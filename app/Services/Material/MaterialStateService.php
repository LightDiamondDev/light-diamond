<?php

namespace App\Services\Material;

use App\Models\Material;
use App\Models\MaterialState;
use App\Services\Material\Dto\MaterialStateDto;
use Auth;

readonly class MaterialStateService
{
    public function __construct(private MaterialLocalizationService $localizationService)
    {
    }

    public function create(Material $material, MaterialStateDto $dto): MaterialState
    {
        $state = MaterialState::make();
        $state->material()->associate($material);
        $state->author_id = $dto->authorId ?? Auth::id();
        $state->save();

        foreach ($dto->localizations as $localizationDto) {
            $this->localizationService->create($state, $localizationDto);
        }

        return $state;
    }

    public function update(MaterialState $state, MaterialStateDto $dto): void
    {
        if ($dto->authorId !== null) {
            $state->author_id = $dto->authorId;
        }

        if ($dto->localizations !== null) {
            $localizationIds = collect($dto->localizations)
                ->pluck('id')
                ->filter()
                ->all();

            $removedLocalizationKeys = $state->localizations
                ->reject(fn($localization) => in_array($localization->id, $localizationIds))
                ->each(fn($oldLocalization) => $this->localizationService->delete($oldLocalization))
                ->keys();
            $state->localizations->forget($removedLocalizationKeys);

            foreach ($dto->localizations as $localizationDto) {
                if ($localizationDto->id === null) {
                    $newLocalization = $this->localizationService->create($state, $localizationDto);
                    $state->localizations->push($newLocalization);
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

    public function delete(MaterialState $state): void
    {
        $state->delete();
    }
}
