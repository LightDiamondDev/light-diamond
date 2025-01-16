<?php

namespace App\Services\MaterialVersion\Dto;

use App\Models\Enums\SubmissionType;
use App\Services\DtoTrait;
use App\Services\MaterialFile\Dto\MaterialFileSubmissionDto;

class MaterialVersionSubmissionDto
{
    use DtoTrait;

    /**
     * @param MaterialFileSubmissionDto[]|null $fileSubmissions
     */
    public function __construct(
        public ?int                     $id = null,
        public ?int                     $versionId = null,
        public ?MaterialVersionStateDto $versionState = null,
        public ?SubmissionType          $type = null,
        public ?array                   $fileSubmissions = null
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return self::fromArrayInternal($data, function ($key, $value) {
            return match ($key) {
                'versionState'    => MaterialVersionStateDto::fromArray($value),
                'type'            => SubmissionType::from($value),
                'fileSubmissions' => array_map(
                    [MaterialFileSubmissionDto::class, 'fromArray'],
                    $value
                ),
                default           => $value,
            };
        });
    }
}
