<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\User\UserLoginResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $this->handleLanguage('cs');
        $data = $request->all();

        $validator = Validator::make($data, [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $remember = $request->boolean('remember', false);
        $expiration = $remember ? now()->addYear() : now()->addDays(3);
        $token = $user->createToken('auth_token', ['*'], $expiration)->plainTextToken;

        return Response::json(['token' => $token]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        $token = $request->user()->createToken('auth_token')->plainTextToken;

        return Response::json(['token' => $token]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return Response::json();
    }

    public function me(Request $request): JsonResponse
    {
        return Response::json(UserLoginResource::make($request->user()));
    }
}
