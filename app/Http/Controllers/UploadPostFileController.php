<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\File;

class UploadPostFileController extends Controller
{
    use ApiJsonResponseTrait;

    const MAX_FILE_SIZE_MB = 20;

    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                File::types(['zip', 'mcpack', 'mcaddon', 'mcworld', 'mcpack', 'png']),
                File::default()
                    ->max(self::MAX_FILE_SIZE_MB * 1024)
            ],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $file = $request->file('file');
        $filePath = $file->store('files', ['disk' => 'private']);

        return $this->successJsonResponse([
            'file_path' => $filePath
        ]);
    }
}
