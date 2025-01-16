<?php

namespace App\Rules;

use App\Models\Material;
use App\Models\MaterialFile;
use App\Models\Scopes\PublishedScope;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

readonly class MaterialFileRule implements ValidationRule
{
    public function __construct(private ?Material $material = null)
    {
    }

    /**
     * @param array $value Информация о файле (id, path, url, size, extension)
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $id = $value['id'] ?? null;
        if ($id !== null) {
            $materialFile = MaterialFile::withoutGlobalScope(PublishedScope::class)->find($id);
            if ($materialFile === null) {
                $fail("Файл с id $id не найден.");
                return;
            }

            preg_match('/^version_submissions\.\d+/', $attribute, $matches);
            $versionId = data_get(request(), $matches[0] . '.version_id');

            if ($materialFile->version_id !== $versionId) {
                $fail("Файл с id $id не относится к версии c id $versionId.");
            }
        } else {
            $isPathLocation = isset($value['path']);
            $isUrlLocation  = isset($value['url']);
            if (!$isPathLocation && !$isUrlLocation) {
                $fail("Не указан ни путь, ни URL файла.");
                return;
            }

            if ($isPathLocation) {
                $path = $value['path'];

                $sameFileInAnotherMaterial = MaterialFile::where('path', $path);
                if ($this->material !== null) {
                    $sameFileInAnotherMaterial
                        ->join('material_versions', 'material_versions.id', '=', 'material_files.version_id')
                        ->join('materials', 'materials.id', '=', 'material_versions.material_id')
                        ->whereNot('materials.id', $this->material->id);
                }
                if ($sameFileInAnotherMaterial->exists()) {
                    $fail("Файл по пути $path используется в другом материале.");
                    return;
                }

                if (!Storage::disk('private')->exists($path)) {
                    $fail('Файла по пути ' . $path . ' не существует.');
                }
            } else {
                $url = $value['url'];
                if (!Str::isUrl($url)) {
                    $fail("Некорректный URL-адрес файла: $url.");
                }
            }
        }
    }
}
