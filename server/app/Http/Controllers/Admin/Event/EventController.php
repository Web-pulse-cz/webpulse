<?php

namespace App\Http\Controllers\Admin\Event;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Event\EventResource;
use App\Models\Event\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Event::query()
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where('code', 'like', '%' . $searchString . '%')
                    ->orWhere('place', 'like', '%' . $searchString . '%')
                    ->orWhereTranslation('name', 'like', '%' . $searchString . '%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $events = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => EventResource::collection($events->items()),
                'total' => $events->total(),
                'perPage' => $events->perPage(),
                'currentPage' => $events->currentPage(),
                'lastPage' => $events->lastPage(),
            ]);

        }
        $events = $query->get();
        return Response::json(EventResource::collection($events));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, int $id = null)
    {
        if ($id) {
            $event = Event::find($id);
            if (!$event) {
                App::abort(404);
            }
        } else {
            $event = new Event();
        }

        $validator = Validator::make($request->all(), [
            'registration_from' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'translations' => 'required|array',
            'translations.*.name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $event->fill($request->all());
            if (!$id) {
                $event->generateCode();
            }

            if($request->has('registration_from') && !in_array($request->get('registration_from'), [null, ''])) {
                $event->registration_from = Carbon::parse($request->get('registration_from'));
            } else {
                $event->registration_from = null;
            }

            if($request->has('start_date') && !in_array($request->get('start_date'), [null, ''])) {
                $event->start_date = Carbon::parse($request->get('start_date'));
            } else {
                $event->start_date = null;
            }

            if($request->has('end_date') && !in_array($request->get('end_date'), [null, ''])) {
                $event->end_date = Carbon::parse($request->get('end_date'));
            } else {
                $event->end_date = null;
            }

            foreach ($request->translations as $locale => $translation) {
                $translation['slug'] = Str::slug($translation['name']);
                $event->translateOrNew($locale)->fill($translation);
            }

            $event->save();

            $event->saveImages($event, $request->get('image'));
            $event->saveSites($event, $request->get('sites', []));

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['error' => 'An error occurred while saving the event.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if (!$id) {
            App::abort(400);
        }

        $event = Event::with(['registrations'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);;
        if (!$event) {
            App::abort(404);
        }

        return Response::json(EventResource::make($event));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $event = Event::find($id);
        if (!$event) {
            App::abort(404);
        }

        $event->delete();
        return Response::json();
    }
}
