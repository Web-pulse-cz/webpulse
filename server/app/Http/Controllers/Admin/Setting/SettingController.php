<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Setting\SettingResource;
use App\Models\Setting\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Setting::query()
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where('type', 'like', '%' . $searchString . '%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $settings = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => SettingResource::collection($settings->items()),
                'total' => $settings->total(),
                'perPage' => $settings->perPage(),
                'currentPage' => $settings->currentPage(),
                'lastPage' => $settings->lastPage(),
            ]);
        }

        $settings = $query->get();
        return Response::json(SettingResource::collection($settings));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $setting = Setting::find($id);
            if (!$setting) {
                App::abort(404);
            }
        } else {
            $setting = new Setting();
        }

        $validator = Validator::make($request->all(), [
            'translations' => 'required|array',
            'translations.*.value' => 'required',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $setting->fill($request->all());

            foreach ($request->translations as $locale => $translation) {
                $setting->translateOrNew($locale)->fill($translation);
            }

            $setting->saveSites($setting, $request->get('sites', []));

            $setting->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating setting.'], 500);
        }

        return Response::json(SettingResource::make($setting));
    }

    public function show(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $setting = Setting::find($id);
        if (!$setting) {
            App::abort(404);
        }

        return Response::json(SettingResource::make($setting));
    }

    public function destroy(int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $setting = Setting::find($id);
        if (!$setting) {
            App::abort(404);
        }

        $setting->delete();
        return Response::json();
    }
}
