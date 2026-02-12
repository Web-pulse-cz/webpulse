<?php

namespace App\Http\Controllers\Admin\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Admin\Site\SiteResource;
use Illuminate\Support\Str;

class SiteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Site::query();

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where('name', 'like', '%' . $searchString . '%')
                    ->orWhere('url', 'like', '%' . $searchString . '%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        } else {
            $query->orderBy('name', 'asc');
        }

        if ($request->has('paginate')) {
            $sites = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => SiteResource::collection($sites->items()),
                'total' => $sites->total(),
                'perPage' => $sites->perPage(),
                'currentPage' => $sites->currentPage(),
                'lastPage' => $sites->lastPage(),
            ]);
        }

        $sites = $query->get();
        return Response::json(SiteResource::collection($sites));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $site = Site::find($id);

            if (!$site) {
                App::abort(404);
            }
        } else {
            $site = new Site();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'url' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $site->fill($request->all());
            if (!$id) {
                $site->hash = Str::random(128);
            }
            $site->save();

            DB::table('sites_has_users')->where('site_id', $site->id)->delete();
            foreach ($request->users as $user) {
                DB::table('sites_has_users')->insert([
                    'user_id' => $user['id'],
                    'site_id' => $site->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            return Response::json(['message' => 'An error occurred while updating site.'], 500);
        }

        return Response::json(SiteResource::make($site));
    }

    public function show(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $site = Site::find($id);

        if (!$site) {
            App::abort(404);
        }

        return Response::json(SiteResource::make($site));
    }

    public function destroy(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $site = Site::find($id);

        if (!$site) {
            App::abort(404);
        }
        DB::table('sites_has_users')->where('site_id', $site->id)->delete();
        DB::table('siteables')->where('site_id', $site->id)->delete();
        $site->delete();

        return Response::json([]);
    }
}
