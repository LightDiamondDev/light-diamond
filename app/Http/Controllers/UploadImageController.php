<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Intervention\Image\Laravel\Facades\Image;
use Spatie\ImageOptimizer\OptimizerChain;

class UploadImageController extends Controller
{
    use ApiJsonResponseTrait;

    const int MAX_IMAGE_SIZE_MB = 5;
    const int MIN_IMAGE_WIDTH   = 100;
    const int MIN_IMAGE_HEIGHT  = 100;
    const int MAX_IMAGE_WIDTH   = 1920;
    const int MAX_IMAGE_HEIGHT  = 1080;

    public function __construct(private readonly OptimizerChain $imageOptimizer)
    {
    }

    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => [
                'required',
                File::types(['jpeg', 'jpg', 'png', 'gif']),
                File::image()
                    ->max(self::MAX_IMAGE_SIZE_MB * 1024)
                    ->dimensions(
                        Rule::dimensions()
                            ->minWidth(self::MIN_IMAGE_WIDTH)
                            ->minHeight(self::MIN_IMAGE_HEIGHT)
                    ),
            ],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $imageFile = $request->file('image');

        if ($imageFile->getClientOriginalExtension() === 'gif') {
            $path = $imageFile->store('images', ['disk' => 'public']);
        } else {
            $image = Image::read($imageFile);
            $image->scaleDown(self::MAX_IMAGE_WIDTH, self::MAX_IMAGE_HEIGHT);

            $path = 'images/' . $imageFile->hashName();
            Storage::disk('public')->put($path, $image->encode());
        }

        $this->imageOptimizer->optimize(Storage::disk('public')->path($path));

        return $this->successJsonResponse([
            'image_path' => $path,
            'image_url'  => url(Storage::url($path)),
        ]);
    }
}
