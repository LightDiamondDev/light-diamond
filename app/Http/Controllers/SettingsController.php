<?php

namespace App\Http\Controllers;

use App\Rules\NotVerifiedEmailRule;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Intervention\Image\Laravel\Facades\Image;
use Spatie\ImageOptimizer\OptimizerChain;

class SettingsController extends Controller
{
    use ApiJsonResponseTrait;
    use PasswordValidationRulesTrait;
    use UsernameValidationRulesTrait;

    const int    MAX_AVATAR_SIZE_MB = 5;
    const int    MIN_AVATAR_WIDTH   = 100;
    const int    MIN_AVATAR_HEIGHT  = 100;
    const int    MAX_AVATAR_WIDTH   = 512;
    const string AVATARS_DIR        = 'avatars/';

    public function __construct(private readonly OptimizerChain $imageOptimizer)
    {
    }

    public function sendEmailVerificationLink(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return $this->errorJsonResponse('E-mail данного пользователя уже подтверждён.');
        }

        $user->sendEmailVerificationNotification();

        return $this->successJsonResponse();
    }

    public function changeAvatar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'avatar' => [
                'required',
                File::types(['jpeg', 'jpg', 'png']),
                File::image()
                    ->max(self::MAX_AVATAR_SIZE_MB * 1024)
                    ->dimensions(
                        Rule::dimensions()
                            ->minWidth(self::MIN_AVATAR_WIDTH)
                            ->minHeight(self::MIN_AVATAR_HEIGHT)
                    ),
            ],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $imageFile = $request->file('avatar');

        $image = Image::read($imageFile);
        $image->coverDown(self::MAX_AVATAR_WIDTH, self::MAX_AVATAR_WIDTH);

        $user = Auth::user();

        if ($user->avatar !== null) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = self::AVATARS_DIR . $imageFile->hashName();
        Storage::disk('public')->put($path, $image->encode());

        $user->avatar = $path;
        $user->save();

        $this->imageOptimizer->optimize(Storage::disk('public')->path($path));

        return $this->successJsonResponse(['avatar_url' => $user->avatar_url]);
    }

    public function changeUsername(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => $this->getUsernameRules(),
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $user           = Auth::user();
        $user->username = $request->get('username');
        $user->save();

        return $this->successJsonResponse();
    }

    public function changeEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'              => ['required', 'email', 'confirmed', new NotVerifiedEmailRule()],
            'email_confirmation' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $email = $request->get('email');

        $user                    = Auth::user();
        $user->email             = $email;
        $user->email_verified_at = null;
        $user->save();

        $user->sendEmailVerificationNotification();

        return $this->successJsonResponse();
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password'              => $this->getPasswordRules(),
            'password_confirmation' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $user           = Auth::user();
        $user->password = $request->get('password');
        $user->save();

        return $this->successJsonResponse();
    }
}
