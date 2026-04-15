<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Response;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return Response::json(['message' => 'Odkaz pro obnovení hesla byl odeslán na váš e-mail.']);
        }

        return Response::json(['message' => 'Nepodařilo se odeslat odkaz pro obnovení hesla. Zkontrolujte zadaný e-mail.'], 422);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                $user->tokens()->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return Response::json(['message' => 'Heslo bylo úspěšně obnoveno.']);
        }

        return Response::json(['message' => 'Nepodařilo se obnovit heslo. Odkaz mohl vypršet nebo je neplatný.'], 422);
    }
}
