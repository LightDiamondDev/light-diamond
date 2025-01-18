<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VerifyCaptcha
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('services.hcaptcha.enabled') || Auth::user()?->is_moderator) {
            return $next($request);
        }

        $token = $request->cookie('captcha_token');

        if (!$token) {
            return response()->json(['error' => 'Captcha token is required'], 403);
        }

        if (Cache::has('captcha_' . $token)) {
            return $next($request);
        }

        return response()->json(['error' => 'Captcha token is expired or invalid'], 403);
    }
}
