<?php

namespace App\Services\MaterialFile\Dto;

use App\Services\DtoTrait;

class MaterialFileStateDto
{
    use DtoTrait;

    /**
     * @param MaterialFileLocalizationDto[]|null $localizations
     */
    public function __construct(public ?array $localizations = null)
    {
    }

    public static function fromArray(array $data): self
    {
        return self::fromArrayInternal($data, function ($key, $value) {
            return match ($key) {
                'localizations' => array_map(
                    [MaterialFileLocalizationDto::class, 'fromArray'],
                    $value
                ),
                default         => $value,
            };
        });
    }
}
