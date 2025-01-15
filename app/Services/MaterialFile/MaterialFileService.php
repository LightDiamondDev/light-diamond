<?php

namespace App\Services\MaterialFile;

use App\Models\MaterialFile;
use App\Models\MaterialVersion;
use App\Services\MaterialFile\Dto\MaterialFileDto;
use Illuminate\Support\Facades\Storage;

class MaterialFileService
{
    public function create(MaterialVersion $version, MaterialFileDto $dto): MaterialFile
    {
        $file = MaterialFile::make();
        $file->version()->associate($version);
        $file->path      = $dto->path;
        $file->url       = $dto->path === null ? $dto->url : null;
        $file->size      = $dto->path === null ? $dto->size : Storage::disk('private')->size($dto->path);
        $file->extension = $dto->path === null ? $dto->extension : pathinfo($dto->path, PATHINFO_EXTENSION);
        if ($file->extension !== null) {
            $file->extension = strtolower($file->extension);
        }
        $file->save();

        return $file;
    }

    public function update(MaterialFile $file, MaterialFileDto $dto): void
    {
        if ($file->path === null) {
            if ($dto->url !== null) {
                $file->url = $dto->url;
            }
            if ($dto->size !== null) {
                $file->size = $dto->size;
            }
            if ($dto->extension !== null) {
                $file->extension = strtolower($dto->extension);
            }
        }
        $file->save();
    }

    public function delete(MaterialFile $file): void
    {
        $file->forceDelete();
        if ($file->path !== null && !MaterialFile::where('path', $file->path)->exists()) {
            Storage::disk('private')->delete($file->path);
        }
    }
}
