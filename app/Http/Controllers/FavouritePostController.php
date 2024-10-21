<?php

namespace App\Http\Controllers;

use App\Models\FavouritePost;
use App\Models\Post;
use Auth;
use Illuminate\Http\JsonResponse;

class FavouritePostController extends Controller
{
    use ApiJsonResponseTrait;

    public function addFavourite(int $postId): JsonResponse
    {
        $post = Post::find($postId);
        if ($post === null) {
            return $this->errorJsonResponse("Не найден Материал с id $postId.");
        }

        $user = Auth::user();
        $isAlreadyFavourite = FavouritePost::wherePostId($postId)->whereUserId($user->id)->exists();

        if (!$isAlreadyFavourite) {
            $favourite = FavouritePost::make();
            $favourite->post()->associate($post);
            $favourite->user()->associate($user);
            $favourite->save();
        }

        return $this->successJsonResponse();
    }

    public function removeFavourite(int $postId): JsonResponse
    {
        $post = Post::find($postId);
        if ($post === null) {
            return $this->errorJsonResponse("Не найден Материал с id $postId.");
        }

        $user = Auth::user();
        $favourite = FavouritePost::wherePostId($postId)->whereUserId($user->id)->first();
        $favourite?->delete();

        return $this->successJsonResponse();
    }
}
