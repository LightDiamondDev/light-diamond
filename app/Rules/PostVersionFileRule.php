<?php

namespace App\Rules;

use App\Models\Post;
use App\Models\PostVersion;
use App\Models\PostVersionFile;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder as Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostVersionFileRule implements ValidationRule
{
    public function __construct(private readonly ?Post $post = null, private readonly ?PostVersion $postVersion = null)
    {
    }

    /**
     * @param array $value Информация о файле (id, name, path, url, size)
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $id = $value['id'] ?? null;
        if ($id !== null && $this->postVersion !== null) {
            $postVersionFile = PostVersionFile::find($id);
            if ($postVersionFile === null) {
                $fail("Файл с id $id не найден.");
                return;
            }

            if ($postVersionFile->post_version_id !== $this->postVersion->id) {
                $fail("Файл с id $id не относится к данной версии материала.");
            }
        } else {
            $isPathLocation = array_key_exists('path', $value);

            if ($isPathLocation) {
                $path = $value['path'];

                $sameFileInAnotherPost = PostVersionFile::where('path', $path);
                if ($this->post !== null) {
                    $sameFileInAnotherPost->whereHas('postVersion', function (Builder $query) {
                        $query->whereNot('post_id', $this->post->id);
                    });
                }
                if ($sameFileInAnotherPost->exists()) {
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
