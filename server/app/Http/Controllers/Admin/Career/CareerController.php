<?php

namespace App\Http\Controllers\Admin\Career;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Career\CareerResource;
use App\Models\Career\Career;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CareerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Career::query()
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where('type', '=', $searchString)
                    ->orWhere('status', '=', $searchString)
                    ->orWhereTranslation('name', '=', $searchString);
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $careers = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => CareerResource::collection($careers->items()),
                'total' => $careers->total(),
                'perPage' => $careers->perPage(),
                'currentPage' => $careers->currentPage(),
                'lastPage' => $careers->lastPage(),
            ]);

        }
        $careers = $query->get();
        return Response::json(CareerResource::collection($careers));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $career = Career::find($id);
            if (!$career) {
                App::abort(404);
            }
        } else {
            $career = new Career();
        }

        $validator = Validator::make($request->all(), [
            'translations' => 'required|array',
            'translations.*.name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $career->fill($request->all());
            foreach ($request->translations as $locale => $translation) {
                $translation['slug'] = Str::slug($translation['name']);
                $career->translateOrNew($locale)->fill($translation);
            }
            if (!$id) {
                $career->generateCode();
            }

            $career->saveSites($career, $request->get('sites', []));

            $career->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating career.'], 500);
        }

        return Response::json();
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $career = Career::find($id);
        if (!$career) {
            App::abort(404);
        }

        return Response::json(CareerResource::make($career));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $career = Career::find($id);
        if (!$career) {
            App::abort(404);
        }

        $career->delete();
        return Response::json();
    }
}
