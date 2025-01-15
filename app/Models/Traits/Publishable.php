<?php

namespace App\Models\Traits;

use App\Models\Scopes\PublishedScope;

trait Publishable
{
    public static function bootPublishable(): void
    {
        static::addGlobalScope(new PublishedScope());
    }

    public function published(): bool
    {
        return $this->published_at !== null;
    }
}
