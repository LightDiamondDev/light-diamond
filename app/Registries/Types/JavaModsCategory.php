<?php

namespace App\Registries\Types;

use App\Models\Enums\GameEdition;
use App\Registries\CategoryType;

class JavaModsCategory implements Category
{
    public function getType(): CategoryType
    {
        return CategoryType::JavaMods;
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
