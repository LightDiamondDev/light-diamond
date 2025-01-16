<?php

namespace App\Services\MaterialFile\Dto;

use App\Models\Enums\SubmissionType;
use App\Services\DtoTrait;

class MaterialFileSubmissionDto
{
    use DtoTrait;

    public function __construct(
        public ?int                  $id = null,
        public ?MaterialFileDto      $file = null,
        public ?MaterialFileStateDto $fileState = null,
        public ?SubmissionType       $type = null,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return self::fromArrayInternal($data, function ($key, $value) {
            return match ($key) {
                'file'      => MaterialFileDto::fromArray($value),
                'fileState' => MaterialFileStateDto::fromArray($value),
                'type'      => SubmissionType::from($value),
                default     => $value,
            };
        });
    }
}
