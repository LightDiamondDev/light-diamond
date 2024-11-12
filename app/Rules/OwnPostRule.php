<?php

namespace App\Rules;

use App\Models\Post;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class OwnPostRule implements ValidationRule
{
    /**
     * @param int $value Id материала
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var Post|null $post */
        $post = Post::find($value);

        if ($post === null || $post->version->author_id !== Auth::user()->id) {
            $fail("Материал с id $value не существует или не принадлежит вам.");
        }
    }
}
