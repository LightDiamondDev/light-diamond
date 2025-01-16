<?php

namespace App\Http\Controllers;

use App\Models\MaterialComment;
use App\Models\MaterialCommentLike;
use Auth;
use Illuminate\Http\JsonResponse;

class MaterialCommentLikeController extends Controller
{
    use ApiJsonResponseTrait;

    public function like(int $commentId): JsonResponse
    {
        $exists = MaterialComment::whereId($commentId)->exists();
        if (!$exists) {
            return $this->errorJsonResponse("Комментарий с id $commentId не найден.");
        }

        $user           = Auth::user();
        $isAlreadyLiked = MaterialCommentLike::whereCommentId($commentId)->whereUserId($user->id)->exists();

        if (!$isAlreadyLiked) {
            $like             = MaterialCommentLike::make();
            $like->comment_id = $commentId;
            $like->user_id    = $user->id;
            $like->save();
        }

        return $this->successJsonResponse();
    }

    public function unlike(int $commentId): JsonResponse
    {
        $exists = MaterialComment::whereId($commentId)->exists();
        if (!$exists) {
            return $this->errorJsonResponse("Комментарий с id $commentId не найден.");
        }

        $user = Auth::user();
        MaterialCommentLike::whereCommentId($commentId)->whereUserId($user->id)->delete();

        return $this->successJsonResponse();
    }
}
