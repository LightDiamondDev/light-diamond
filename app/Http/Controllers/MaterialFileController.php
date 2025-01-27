<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialFile;
use App\Models\MaterialFileDownload;
use App\Models\MaterialSubmission;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaterialFileController extends Controller
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

        $fileBaseName  = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileExtension = $file->getClientOriginalExtension();
        $filename      = $fileBaseName . '.' . $fileExtension;
        for ($i = 1; Storage::disk('private')->exists('files/' . $filename); $i++) {
            $filename = $fileBaseName . '_' . Str::random(5) . '.' . $fileExtension;
        }

        $filePath = $file->storeAs('files', $filename, ['disk' => 'private']);

        return $this->successJsonResponse([
            'file_path' => $filePath
        ]);
    }

    public function download(int $versionId, int $fileId): StreamedResponse|JsonResponse
    {
        $materialFile = MaterialFile::withoutGlobalScopes()->find($fileId);

        if ($materialFile === null || $materialFile->version_id !== $versionId) {
            abort(404);
        }

        if ($materialFile->trashed() || !$materialFile->published()) {
            $user        = Auth::user();
            $fileOwnerId = null;

            if ($user !== null) {
                $fileOwnerId = MaterialSubmission::withoutGlobalScopes()
                    ->join('material_version_submissions', 'material_version_submissions.material_submission_id', '=', 'material_submissions.id')
                    ->join('material_file_submissions', 'material_file_submissions.version_submission_id', '=', 'material_version_submissions.id')
                    ->join('material_files', 'material_files.id', '=', 'material_file_submissions.file_id')
                    ->where('material_files.id', $fileId)
                    ->orderByDesc('material_submissions.id')
                    ->pluck('material_submissions.submitter_id')
                    ->first();
            }

            if ($user === null || $user->id !== $fileOwnerId && !$user->is_moderator) {
                abort(404);
            }
        }

        $ip = request()->ip();

        $isFirstFileDownload = !MaterialFileDownload::whereFileId($materialFile->id)->whereIp($ip)->exists();
        if ($isFirstFileDownload) {
            $materialVersion         = $materialFile->version;
            $isFirstMaterialDownload = !MaterialFileDownload::ofMaterial($materialVersion->material_id)->whereIp($ip)->exists();
            if ($isFirstMaterialDownload) {
                Material::withoutTimestamps(function () use ($materialVersion) {
                    Material::withoutGlobalScopes()->whereId($materialVersion->material_id)->increment('downloads_count');
                });
            }

            $newVersionDownload     = MaterialFileDownload::make();
            $newVersionDownload->ip = $ip;
            $newVersionDownload->file()->associate($materialFile);
            $newVersionDownload->save();
        }

        if ($materialFile->path !== null) {
            return Storage::disk('private')->download($materialFile->path, basename($materialFile->path));
        }

        return response()->json([
            'download_url' => $materialFile->url,
        ]);
    }
}
