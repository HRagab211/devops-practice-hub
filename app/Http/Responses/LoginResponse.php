<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;

class LoginResponse implements LoginResponseContract
{
    /**
     * Send AJAX logins to the intended URL the same way a regular form post would be redirected.
     *
     * @param  Request  $request
     */
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        $redirect = redirect()->intended(Fortify::redirects('login'));

        return $request->wantsJson()
            ? response()->json(['message' => 'Welcome back.', 'redirect' => $redirect->getTargetUrl()])
            : $redirect;
    }
}
