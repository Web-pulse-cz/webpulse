<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\User\UserGroupResource;
use App\Models\User\UserGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class UserGroupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = UserGroup::query();

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%'.$searchString[1].'%');
            } else {
                $query->where('name', 'like', '%'.$searchString.'%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $userGroups = $query->paginate($request->get('paginate'));

            foreach ($userGroups as $userGroup) {
                $userGroup->users_count = $userGroup->users()->count();
            }

            return Response::json([
                'data' => UserGroupResource::collection($userGroups->items()),
                'total' => $userGroups->total(),
                'perPage' => $userGroups->perPage(),
                'currentPage' => $userGroups->currentPage(),
                'lastPage' => $userGroups->lastPage(),
            ]);
        }

        $userGroups = $query->get();

        return Response::json(UserGroupResource::collection($userGroups));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $userGroup = UserGroup::find($id);
            if (! $userGroup) {
                App::abort(404);
            }
        } else {
            $userGroup = new UserGroup;
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            if ($request->has('new_password') && $request->get('new_password') == $request->get('confirm_new_password')) {
                $userGroup->password = Hash::make($request->get('new_password'));
            }

            $userGroup->fill($request->all());
            if ($request->has('permissions')) {
                $userGroup->permissions = json_encode($request->get('permissions'));
            }
            $userGroup->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();

            return Response::json(['error' => $e->getMessage()], 500);
        }

        return Response::json(UserGroupResource::make($userGroup));
    }

    public function show(int $id): JsonResponse
    {
        if (! $id) {
            App::abort(400);
        }

        $userGroup = UserGroup::find($id);
        if (! $userGroup) {
            App::abort(404);
        }

        return Response::json(UserGroupResource::make($userGroup));
    }

    public function destroy(int $id)
    {
        if (! $id) {
            App::abort(400);
        }

        $userGroup = UserGroup::find($id);
        if (! $userGroup) {
            App::abort(404);
        }

        $userGroup->delete();

        return Response::json();
    }
}
