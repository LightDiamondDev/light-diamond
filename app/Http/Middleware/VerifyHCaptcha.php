<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class VerifyHCaptcha
{
    const int TOKEN_EXPIRE_MINUTES = 10;

    public function handle(Request $request, Closure $next)
    {
        if (!config('services.hcaptcha.enabled') || Auth::user()?->is_moderator) {
            return $next($request);
        }

        $token = $request->cookie('hcaptcha_token');

        if (!$token) {
            return response()->json(['error' => 'Captcha token is required'], 403);
        }

        if (Cache::has($token)) {
            return $next($request);
        }

        $response = Http::asForm()->post('https://hcaptcha.com/siteverify', [
            'secret'   => config('services.hcaptcha.secret'),
            'response' => $token,
            'remoteip' => $request->ip(),
            'sitekey'  => config('services.hcaptcha.sitekey'),
        ]);

        $result = $response->json();

        if (!$result['success'] || empty($result['challenge_ts'])) {
            return response()->json(['error' => 'Invalid captcha token'], 403);
        }

        Cache::put(
            $token,
            $result['challenge_ts'],
            now()->addMinutes(self::TOKEN_EXPIRE_MINUTES)
        );

        return $next($request);
    }
}
