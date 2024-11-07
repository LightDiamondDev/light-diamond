<?php

namespace App\Registries\Types;

use App\Models\Enums\GameEdition;
use App\Registries\CategoryType;

class JavaDataPacksCategory implements Category
{
    public function getType(): CategoryType
    {
        return CategoryType::JavaDataPacks;
    }

    public function getEdition(): ?GameEdition
    {
        return GameEdition::Java;
    }

    public function isDownloadable(): bool
    {
        return true;
    }
}
