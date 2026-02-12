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
use Illuminate\Support\Str;

class UserController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where('firstname', 'like', '%' . $searchString . '%')
                    ->orWhere('lastname', 'like', '%' . $searchString . '%')
                    ->orWhere('email', 'like', '%' . $searchString . '%')
                    ->orWhere('phone', 'like', '%' . $searchString . '%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $users = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => UserResource::collection($users->items()),
                'total' => $users->total(),
                'perPage' => $users->perPage(),
                'currentPage' => $users->currentPage(),
                'lastPage' => $users->lastPage(),
            ]);
        }

        $users = $query->get();
        return Response::json(\App\Http\Resources\Admin\User\UserResource::collection($users));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $user = \App\Models\User\User::find($id);
            if (!$user) {
                App::abort(404);
            }
        } else {
            $user = new User();
        }

        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'required|string|max:255|unique:users,phone,' . $id,
            'user_group_id' => 'required|integer|exists:user_groups,id',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            if ($request->has('new_password') && $request->get('new_password') == $request->get('confirm_new_password')) {
                $user->password = Hash::make($request->get('new_password'));
            }


            $user->fill($request->all());
            if (!$id) {
                $user->invitation_token = $this->generateUnqiueToken();
            }
            $user->user_group_id = $request->get('user_group_id');
            $user->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['error' => $e->getMessage()], 500);
        }

        return Response::json(\App\Http\Resources\Admin\User\UserResource::make($user));
    }

    public function show(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $user = User::find($id);
        if (!$user) {
            App::abort(404);
        }

        return Response::json(UserResource::make($user));
    }

    public function destroy(int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $user = User::find($id);
        if (!$user) {
            App::abort(404);
        }

        $user->delete();
        return Response::json();
    }

    private function generateUnqiueToken(): string
    {
        $token = Str::upper(Str::random(8));
        $user = \App\Models\User\User::where('invitation_token', $token)->exists();

        if ($user) {
            self::generateUnqiueToken();
        }

        return $token;
    }
}
