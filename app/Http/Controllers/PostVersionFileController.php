<?php

namespace App\Http\Controllers;

use App\Models\PostVersionFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PostVersionFileController extends Controller
{
    use ApiJsonResponseTrait;

    const MAX_FILE_SIZE_MB = 20;

    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                File::types(['zip', 'mcaddon', 'mcworld', 'mcpack', 'png']),
                File::default()
                    ->max(self::MAX_FILE_SIZE_MB * 1024)
            ],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $file = $request->file('file');

        $fileBaseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileExtension = $file->getClientOriginalExtension();
        $filename = $fileBaseName . '.' . $fileExtension;
        for ($i = 1; Storage::disk('private')->exists('files/' . $filename); $i++) {
            $filename = $fileBaseName . '_' . Str::random(5) . '.' . $fileExtension;
        }

        $filePath = $file->storeAs('files', $filename, ['disk' => 'private']);

        return $this->successJsonResponse([
            'file_path' => $filePath
        ]);
    }

    public function download(Request $request, int $versionId, int $fileId): StreamedResponse
    {
        $postVersionFile = PostVersionFile::find($fileId);

        if ($postVersionFile === null || $postVersionFile->post_version_id !== $versionId || $postVersionFile->path === null) {
            abort(404, 'Файл для скачивания не найден.');
        }

        return Storage::disk('private')->download($postVersionFile->path, basename($postVersionFile->path));
    }
}
