<?php

namespace App\Services;

use Closure;

trait DtoTrait
{
    public static function fromArray(array $data): self
    {
        return self::fromArrayInternal($data);
    }

    protected final static function fromArrayInternal(array $data, ?Closure $processFieldCallback = null): self
    {
        $processFieldCallback ??= fn($key, $value) => $value;

        $dtoData = [];
        foreach ($data as $key => $value) {
            $propertyName = lcfirst(self::snakeToCamel($key));
            if (property_exists(static::class, $propertyName)) {
                $dtoData[$propertyName] = $value !== null ? $processFieldCallback($propertyName, $value) : null;
            }
        }

        return new static(...$dtoData);
    }

    private static function snakeToCamel($string): string
    {
        return str_replace('_', '', ucwords($string, '_'));
    }
}
