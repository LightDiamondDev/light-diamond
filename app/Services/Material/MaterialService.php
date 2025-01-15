<?php

namespace App\Services\Material;

use App\Models\Material;
use App\Registries\CategoryRegistry;
use App\Services\Material\Dto\MaterialDto;

readonly class MaterialService
{
    public function __construct(private CategoryRegistry $categoryRegistry)
    {
    }

    public function create(MaterialDto $dto): Material
    {
        $material           = Material::make();
        $material->slug     = strtolower($dto->slug) ?? '';
        $material->category = $dto->category;
        $material->edition  = $dto->edition ?? $this->categoryRegistry->get($dto->category)->getEdition();
        $material->save();

        return $material;
    }

    public function update(Material $material, MaterialDto $dto): void
    {
        if ($dto->slug !== null) {
            $material->slug = $dto->slug;
        }
        if ($dto->category !== null) {
            $material->category = $dto->category;
        }
        if ($dto->edition !== null) {
            $material->edition = $dto->edition;
        }
        $material->save();
    }

    public function delete(Material $material): void
    {
        $material->forceDelete();
    }
}
