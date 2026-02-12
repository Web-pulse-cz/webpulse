<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'required|string|max:255|unique:users',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $data['password'] = Hash::make($data['password']);
        $data['invitation_token'] = $this->generateUnqiueToken();

        try {
            DB::beginTransaction();

            $user = new User();
            $user->fill($data);
            $user->save();

            //TODO:: send email verification
            //Artisan::call(sprintf('app:registration --userId=%s', $user->id));

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['error' => $e->getMessage()], 500);
        }

        return Response::json();
    }

    private function generateUnqiueToken(): string
    {
        $token = Str::upper(Str::random(8));
        $user = User::where('invitation_token', $token)->exists();

        if ($user) {
            self::generateUnqiueToken();
        }

        return $token;
    }
}
