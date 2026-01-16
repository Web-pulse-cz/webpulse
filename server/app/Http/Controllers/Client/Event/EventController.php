<?php

namespace App\Http\Controllers\Client\Event;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Event\EventListResource;
use App\Http\Resources\Client\Event\EventResource;
use App\Models\Event\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Event::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->with(['category'])
            ->where('status', 'published');

        if($request->has('search') && in_array($request->input('search'), ['', null])) {
            $query->where('code', 'like', '%' . $request->input('search') . '%')
                ->orWhere('place', 'like', '%' . $request->input('search') . '%')
                ->orWhereTranslation('name', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->has('category')) {
            $query->where('category_id', $request->input('category'));
        }

        if ($request->has('paginate')) {
            $events = $query->paginate((int)$request->get('paginate'));

            return Response::json([
                'data' => EventListResource::collection($events->items()),
                'total' => $events->total(),
                'perPage' => $events->perPage(),
                'currentPage' => $events->currentPage(),
                'lastPage' => $events->lastPage(),
            ]);
        }

        $events = $query->get();
        return Response::json(EventListResource::collection($events));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if (!$id) {
            return Response::json(['error' => 'Event ID is required'], 400);
        }

        $event = Event::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->where('status', 'published')
            ->where('id', $id)
            ->with(['category', 'currency', 'taxRate'])
            ->first();

        if (!$event) {
            return Response::json(['error' => 'Event not found'], 404);
        }

        return Response::json(EventResource::make($event));
    }
}
