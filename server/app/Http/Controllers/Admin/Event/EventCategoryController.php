<?php

namespace App\Http\Controllers\Admin\Event;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Event\EventCategoryResource;
use App\Models\Event\EventCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = EventCategory::query()
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->whereTranslation('code', '=', $searchString);
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $eventCategories = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => EventCategoryResource::collection($eventCategories->items()),
                'total' => $eventCategories->total(),
                'perPage' => $eventCategories->perPage(),
                'currentPage' => $eventCategories->currentPage(),
                'lastPage' => $eventCategories->lastPage(),
            ]);

        }
        $eventCategories = $query->get();
        return Response::json(EventCategoryResource::collection($eventCategories));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $eventCategory = EventCategory::find($id);
            if (!$eventCategory) {
                App::abort(404);
            }
        } else {
            $eventCategory = new EventCategory();
        }

        $validator = Validator::make($request->all(), [
            'translations' => 'required|array',
            'translations.*.name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $eventCategory->fill($request->all());

            foreach ($request->translations as $locale => $translation) {
                $translation['slug'] = Str::slug($translation['name']);
                $eventCategory->translateOrNew($locale)->fill($translation);
            }

            $eventCategory->saveSites($eventCategory, $request->get('sites', []));

            $eventCategory->save();
            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating faq.'], 500);
        }

        return Response::json(EventCategoryResource::make($eventCategory));
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $eventCategory = EventCategory::find($id);
        if (!$eventCategory) {
            App::abort(404);
        }

        return Response::json(EventCategoryResource::make($eventCategory));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $eventCategory = EventCategory::find($id);

        if (!$eventCategory) {
            App::abort(404);
        }

        $eventCategory->delete();

        return Response::json();
    }
}
