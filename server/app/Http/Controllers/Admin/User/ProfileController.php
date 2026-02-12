<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\User\UserResource;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $authUser = $request->user();

        $user = User::find($authUser->id);

        if (!$user) {
            App::abort(404);
        }

        return Response::json(UserResource::make($user));
    }

    public function store(Request $request): JsonResponse
    {
        $authUser = $request->user();

        $user = User::find($authUser->id);
        if(!$user) {
            App::abort(404);
        }

        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:255|unique:users,phone,' . $user->id,
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $user->fill($request->all());
            $user->save();

            DB::commit();
        }  catch (\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating the user profile.'], 500);
        }

        return Response::json(UserResource::make($user));
    }

    public function password(Request $request): JsonResponse
    {
        $authUser = $request->user();

        $user = User::find($authUser->id);
        if(!$user) {
            App::abort(404);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string',
            'confirm_new_password' => 'required|string|same:new_password',
        ]);

        if ($validator->fails() || !Hash::check($request->current_password, $user->password)) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $user->password = Hash::make($request->new_password);
            $user->save();

            DB::commit();
        }  catch (\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating the user password.'], 500);
        }

        return Response::json(\App\Http\Resources\Admin\User\UserResource::make($user));
    }
}
