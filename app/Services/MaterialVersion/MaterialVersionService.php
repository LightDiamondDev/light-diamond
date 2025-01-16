<?php

namespace App\Services\MaterialVersion;

use App\Models\Material;
use App\Models\MaterialVersion;
use Carbon\Carbon;

readonly class MaterialVersionService
{
    public function create(Material $material, ?Carbon $publishedAt = null): MaterialVersion
    {
        $version = MaterialVersion::make();
        $version->material()->associate($material);
        $version->published_at = $publishedAt;
        $version->save();

        return $version;
    }

    public function delete(MaterialVersion $version): void
    {
        $version->forceDelete();
    }
}
