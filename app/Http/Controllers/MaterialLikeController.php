<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialLike;
use Auth;
use Illuminate\Http\JsonResponse;

class MaterialLikeController extends Controller
{
    use ApiJsonResponseTrait;

    public function like(int $materialId): JsonResponse
    {
        $material = Material::find($materialId);
        if ($material === null) {
            return $this->errorJsonResponse("Материал с id $materialId не найден.");
        }

        $user = Auth::user();
        $isAlreadyLiked = MaterialLike::whereMaterialId($materialId)->whereUserId($user->id)->exists();

        if (!$isAlreadyLiked) {
            $like = MaterialLike::make();
            $like->material()->associate($material);
            $like->user()->associate($user);
            $like->save();
        }

        return $this->successJsonResponse();
    }

    public function unlike(int $materialId): JsonResponse
    {
        $material = Material::find($materialId);
        if ($material === null) {
            return $this->errorJsonResponse("Материал с id $materialId не найден.");
        }

        $user = Auth::user();
        $like = MaterialLike::whereMaterialId($materialId)->whereUserId($user->id)->first();
        $like?->delete();

        return $this->successJsonResponse();
    }
}
