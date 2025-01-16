<?php

namespace App\Http\Controllers;

use App\Models\FavouriteMaterial;
use App\Models\Material;
use Auth;
use Illuminate\Http\JsonResponse;

class FavouriteMaterialController extends Controller
{
    use ApiJsonResponseTrait;

    public function addFavourite(int $materialId): JsonResponse
    {
        $exists = Material::whereId($materialId)->exists();
        if (!$exists) {
            return $this->errorJsonResponse("Материал с id $materialId не найден.");
        }

        $user               = Auth::user();
        $isAlreadyFavourite = FavouriteMaterial::whereMaterialId($materialId)->whereUserId($user->id)->exists();

        if (!$isAlreadyFavourite) {
            $favourite              = FavouriteMaterial::make();
            $favourite->material_id = $materialId;
            $favourite->user_id     = $user->id;
            $favourite->save();
        }

        return $this->successJsonResponse();
    }

    public function removeFavourite(int $materialId): JsonResponse
    {
        $exists = Material::whereId($materialId)->exists();
        if (!$exists) {
            return $this->errorJsonResponse("Материал с id $materialId не найден.");
        }

        $user = Auth::user();
        FavouriteMaterial::whereMaterialId($materialId)->whereUserId($user->id)->delete();

        return $this->successJsonResponse();
    }
}
