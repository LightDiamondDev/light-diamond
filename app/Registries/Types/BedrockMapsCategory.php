<?php

namespace App\Registries\Types;

use App\Models\Enums\GameEdition;
use App\Registries\CategoryType;

class BedrockMapsCategory implements Category
{
    public function getType(): CategoryType
    {
        return CategoryType::BedrockMaps;
    }

    public function getEdition(): ?GameEdition
    {
        return GameEdition::Bedrock;
    }

    public function isDownloadable(): bool
    {
        return true;
    }
}
