<?php

namespace App\Registries\Types;

use App\Models\Enums\GameEdition;
use App\Registries\CategoryType;

class JavaResourcePacksCategory implements Category
{
    public function getType(): CategoryType
    {
        return CategoryType::JavaResourcePacks;
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
