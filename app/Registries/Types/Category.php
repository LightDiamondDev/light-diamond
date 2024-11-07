<?php

namespace App\Registries\Types;

use App\Models\Enums\GameEdition;
use App\Registries\CategoryType;

interface Category
{
    public function getType(): CategoryType;

    public function getEdition(): ?GameEdition;

    public function isDownloadable(): bool;
}
