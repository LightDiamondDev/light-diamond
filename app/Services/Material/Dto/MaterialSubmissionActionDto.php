<?php

namespace App\Services\Material\Dto;

use App\Models\Enums\MaterialSubmissionActionType;
use App\Services\DtoTrait;

class MaterialSubmissionActionDto
{
    use DtoTrait;

    public function __construct(
        public MaterialSubmissionActionType $type,
        public array                        $details = []
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return self::fromArrayInternal($data, function ($key, $value) {
            return match ($key) {
                'type'  => MaterialSubmissionActionType::from($value),
                default => $value,
            };
        });
    }
}
