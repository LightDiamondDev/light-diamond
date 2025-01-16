<?php

namespace App\Services\MaterialFile;

use App\Models\MaterialFile;
use App\Models\MaterialFileState;
use App\Services\MaterialFile\Dto\MaterialFileStateDto;

readonly class MaterialFileStateService
{
    public function __construct(private MaterialFileLocalizationService $localizationService)
    {
    }

    public function create(MaterialFile $file, MaterialFileStateDto $dto): MaterialFileState
    {
        $state = MaterialFileState::make();
        $state->file()->associate($file);
        $state->save();

        foreach ($dto->localizations as $localizationDto) {
            $this->localizationService->create($state, $localizationDto);
        }

        return $state;
    }

    public function update(MaterialFileState $state, MaterialFileStateDto $dto): void
    {
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
    }

    public function delete(MaterialFileState $state): void
    {
        $state->delete();
    }
}
