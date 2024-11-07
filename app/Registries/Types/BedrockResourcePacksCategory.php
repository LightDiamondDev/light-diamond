<?php

namespace App\Registries\Types;

use App\Models\Enums\GameEdition;
use App\Registries\CategoryType;

class BedrockResourcePacksCategory implements Category
{
    public function getType(): CategoryType
    {
        return CategoryType::BedrockResourcePacks;
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
