<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CaptchaTokenController extends Controller
{
    use ApiJsonResponseTrait;

    const int TOKEN_EXPIRE_MINUTES = 10;

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $token = $request->get('token');

        $response = Http::asForm()->post('https://hcaptcha.com/siteverify', [
            'secret'   => config('services.hcaptcha.secret'),
            'response' => $token,
            'remoteip' => $request->ip(),
            'sitekey'  => config('services.hcaptcha.sitekey'),
        ]);

        if ($response->failed()) {
            return $this->errorJsonResponse('Failed to validate captcha.');
        }

        $result = $response->json();

        if (!$result['success'] || empty($result['challenge_ts'])) {
            return $this->errorJsonResponse('Invalid captcha token.');
        }

        $uniqueToken = Str::uuid()->toString();
        $expiry = now()->addMinutes(self::TOKEN_EXPIRE_MINUTES);

        Cache::put('captcha_' . $uniqueToken, true, $expiry);

        return $this->successJsonResponse([
            'expiry' => $expiry->toIso8601String(),
        ])->withCookie(cookie('captcha_token', $uniqueToken, self::TOKEN_EXPIRE_MINUTES));
    }
}
