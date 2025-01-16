<?php

namespace App\Services\Material\Dto;

use App\Models\Enums\SubmissionType;
use App\Services\DtoTrait;
use App\Services\MaterialVersion\Dto\MaterialVersionSubmissionDto;

class MaterialSubmissionCreateDto
{
    use DtoTrait;

    /**
     * @param MaterialVersionSubmissionDto[]|null $versionSubmissions
     */
    public function __construct(
        public MaterialDto      $material,
        public MaterialStateDto $materialState,
        public SubmissionType   $type,
        public ?array           $versionSubmissions = null
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return self::fromArrayInternal($data, function ($key, $value) {
            return match ($key) {
                'material'           => MaterialDto::fromArray($value),
                'materialState'      => MaterialStateDto::fromArray($value),
                'type'               => SubmissionType::from($value),
                'versionSubmissions' => array_map(
                    [MaterialVersionSubmissionDto::class, 'fromArray'],
                    $value
                ),
                default              => $value,
            };
        });
    }
}
