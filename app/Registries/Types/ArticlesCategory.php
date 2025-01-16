<?php

namespace App\Registries\Types;

use App\Models\Enums\GameEdition;
use App\Registries\CategoryType;

class ArticlesCategory implements Category
{
    public function getType(): CategoryType
    {
        return CategoryType::Articles;
    }

    public function getEdition(): ?GameEdition
    {
        return null;
    }

    public function isDownloadable(): bool
    {
        return false;
    }
}
