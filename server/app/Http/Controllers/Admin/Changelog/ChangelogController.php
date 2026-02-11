<?php

namespace App\Http\Controllers\Admin\Changelog;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\Changelog\Changelog;
use App\Http\Resources\Admin\Changelog\ChangelogResource;

class ChangelogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Changelog::query();

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where('version', '=', $searchString)
                    ->orWhere('title', 'like', '%' . $searchString . '%')
                    ->orWhere('subtitle', 'like', '%' . $searchString . '%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $changelogs = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => ChangelogResource::collection($changelogs->items()),
                'total' => $changelogs->total(),
                'perPage' => $changelogs->perPage(),
                'currentPage' => $changelogs->currentPage(),
                'lastPage' => $changelogs->lastPage(),
            ]);
        }

        $changelogs = $query->get();
        return Response::json(ChangelogResource::collection($changelogs));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $changelog = Changelog::find($id);
            if (!$changelog) {
                App::abort(404);
            }
        } else {
            $changelog = new Changelog();
        }

        $validator = Validator::make($request->all(), [
            'version' => 'required|string',
            'title' => 'required|string'
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $changelog->fill($request->all());
            $changelog->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating changelog.'], 500);
        }

        return Response::json(ChangelogResource::make($changelog));
    }

    public function show(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $changelog = Changelog::find($id);
        if (!$changelog) {
            App::abort(404);
        }

        return Response::json(ChangelogResource::make($changelog));
    }

    public function destroy(int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $changelog = Changelog::find($id);
        if (!$changelog) {
            App::abort(404);
        }

        $changelog->delete();
        return Response::json();
    }
}
