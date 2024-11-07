<?php

namespace App\Registries\Types;

use App\Models\Enums\GameEdition;
use App\Registries\CategoryType;

class BedrockAddonsCategory implements Category
{
    public function getType(): CategoryType
    {
        return CategoryType::BedrockAddons;
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
