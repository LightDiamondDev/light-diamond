<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CaptchaTokenController;
use App\Http\Controllers\FavouriteMaterialController;
use App\Http\Controllers\MaterialCommentController;
use App\Http\Controllers\MaterialCommentLikeController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MaterialFileController;
use App\Http\Controllers\MaterialLikeController;
use App\Http\Controllers\MaterialSubmissionController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UploadImageController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/auth/user', fn(Request $request) => $request->user());
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('verify.captcha');
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('verify.captcha');
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('verify.captcha');

Route::get('/users/{username}', [UserController::class, 'getByUsername']);

Route::get('/materials', [MaterialController::class, 'get']);
Route::get('/materials/{slug}', [MaterialController::class, 'getBySlug']);
Route::get('/users/{userId}/materials', [MaterialController::class, 'getUserMaterials'])->where('userId', '[0-9]+');
Route::get('/users/{userId}/favourite-materials', [MaterialController::class, 'getUserFavouriteMaterials'])->where('userId', '[0-9]+');

Route::get('/materials/{materialId}/comments', [MaterialCommentController::class, 'getMaterialComments'])->where('materialId', '[0-9]+');
Route::get('/users/{userId}/comments', [MaterialCommentController::class, 'getUserComments'])->where('userId', '[0-9]+');

Route::get('/material-versions/{versionId}/download/{fileId}', [MaterialFileController::class, 'download'])->middleware('verify.captcha')->where('versionId', '[0-9]+')->where('fileId', '[0-9]+');

Route::post('/captcha-tokens', [CaptchaTokenController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/settings/profile/username', [SettingsController::class, 'changeUsername']);
    Route::put('/settings/security/email', [SettingsController::class, 'changeEmail']);
    Route::put('/settings/security/password', [SettingsController::class, 'changePassword']);
    Route::post('/settings/security/email-verification', [SettingsController::class, 'sendEmailVerificationLink'])->middleware(['throttle:1,1']);

    Route::middleware('verified')->group(function () {
        Route::post('/upload-image', [UploadImageController::class, 'upload'])->middleware('verify.captcha')->middleware(['throttle:10,2']);
        Route::post('/upload-material-file', [MaterialFileController::class, 'upload'])->middleware('verify.captcha')->middleware(['throttle:3,10']);

        Route::get('/auth/user/can-create-submission', [UserController::class, 'canCreateSubmission']);

        Route::get('/material-submissions/{id}', [MaterialSubmissionController::class, 'getById'])->where('id', '[0-9]+');
        Route::post('/material-submissions', [MaterialSubmissionController::class, 'createDraft'])->middleware('verify.captcha');
        Route::post('/material-submissions/submit', [MaterialSubmissionController::class, 'submitNew'])->middleware('verify.captcha');
        Route::patch('/material-submissions/{id}', [MaterialSubmissionController::class, 'update'])->middleware('verify.captcha')->where('id', '[0-9]+');
        Route::patch('/material-submissions/{id}/submit', [MaterialSubmissionController::class, 'submit'])->middleware('verify.captcha')->where('id', '[0-9]+');
        Route::post('/material-submissions/{id}/messages', [MaterialSubmissionController::class, 'message'])->middleware(['verify.captcha', 'throttle:2,1'])->where('id', '[0-9]+');

        Route::get('/users/{userId}/material-submissions', [MaterialSubmissionController::class, 'getByUser'])->where('userId', '[0-9]+');

        Route::post('/materials/{materialId}/likes', [MaterialLikeController::class, 'like'])->where('materialId', '[0-9]+');
        Route::delete('/materials/{materialId}/likes', [MaterialLikeController::class, 'unlike'])->where('materialId', '[0-9]+');

        Route::post('/materials/{materialId}/favourites', [FavouriteMaterialController::class, 'addFavourite'])->where('materialId', '[0-9]+');
        Route::delete('/materials/{materialId}/favourites', [FavouriteMaterialController::class, 'removeFavourite'])->where('materialId', '[0-9]+');

        Route::post('/materials/{materialId}/comments', [MaterialCommentController::class, 'submit'])->middleware(['verify.captcha', 'throttle:4,1'])->where('materialId', '[0-9]+');
        Route::delete('/material-comments/{id}', [MaterialCommentController::class, 'remove'])->where('id', '[0-9]+');
        Route::patch('/material-comments/{id}', [MaterialCommentController::class, 'edit'])->where('id', '[0-9]+');

        Route::post('/material-comments/{id}/likes', [MaterialCommentLikeController::class, 'like'])->where('id', '[0-9]+');
        Route::delete('/material-comments/{id}/likes', [MaterialCommentLikeController::class, 'unlike'])->where('id', '[0-9]+');

        Route::middleware('moderator')->group(function () {
            Route::get('/users', [UserController::class, 'get']);

            Route::get('/material-submissions', [MaterialSubmissionController::class, 'get']);
            Route::put('/material-submissions/{id}/assigned-moderator', [MaterialSubmissionController::class, 'assignModerator'])->where('id', '[0-9]+');
            Route::patch('/material-submissions/{id}/request-changes', [MaterialSubmissionController::class, 'requestChanges'])->where('id', '[0-9]+');
            Route::patch('/material-submissions/{id}/accept', [MaterialSubmissionController::class, 'accept'])->where('id', '[0-9]+');
            Route::patch('/material-submissions/{id}/reject', [MaterialSubmissionController::class, 'reject'])->where('id', '[0-9]+');

            Route::get('/material-comments', [MaterialCommentController::class, 'get']);
        });

        Route::middleware('admin')->group(function () {
            Route::post('/users', [UserController::class, 'add']);
            Route::put('/users/{id}', [UserController::class, 'update'])->where('id', '[0-9]+');;
            Route::delete('/users/{id}', [UserController::class, 'delete'])->where('id', '[0-9]+');;
            Route::delete('/users', [UserController::class, 'deleteMultiple']);
        });
    });
});
