<?php

namespace App\Services\MaterialVersion\Dto;

use App\Services\DtoTrait;

class MaterialVersionStateDto
{
    use DtoTrait;

    /**
     * @param MaterialVersionLocalizationDto[]|null $localizations
     */
    public function __construct(
        public ?string $number = null,
        public ?array  $localizations = null
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return self::fromArrayInternal($data, function ($key, $value) {
            return match ($key) {
                'localizations' => array_map(
                    [MaterialVersionLocalizationDto::class, 'fromArray'],
                    $value
                ),
                default         => $value,
            };
        });
    }
}
