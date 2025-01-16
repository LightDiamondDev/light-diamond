<?php

namespace App\Services\Material\Dto;

use App\Services\DtoTrait;

class MaterialStateDto
{
    use DtoTrait;

    /**
     * @param MaterialLocalizationDto[]|null $localizations
     */
    public function __construct(
        public ?int   $authorId = null,
        public ?array $localizations = null
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return self::fromArrayInternal($data, function ($key, $value) {
            return match ($key) {
                'localizations' => array_map(
                    [MaterialLocalizationDto::class, 'fromArray'],
                    $value
                ),
                default         => $value,
            };
        });
    }
}
