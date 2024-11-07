<?php

namespace App\Registries\Types;

use App\Models\Enums\GameEdition;
use App\Registries\CategoryType;

class SkinsCategory implements Category
{
    public function getType(): CategoryType
    {
        return CategoryType::Skins;
    }

    public function getEdition(): ?GameEdition
    {
        return null;
    }

    public function isDownloadable(): bool
    {
        return true;
    }
}
