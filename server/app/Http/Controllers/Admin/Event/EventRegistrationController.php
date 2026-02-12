<?php

namespace App\Http\Controllers\Admin\Event;

use App\Events\EventRegistrationSaved;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Event\EventRegistrationResource;
use App\Models\Event\EventRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class EventRegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = EventRegistration::query();

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where('email', 'like', '%' . $searchString . '%')
                    ->orWhere('firstname', 'like', '%' . $searchString . '%')
                    ->orWhere('lastname', 'like', '%' . $searchString . '%')
                    ->orWhere('phone', 'like', '%' . $searchString . '%')
                    ->orWhereHas('event', function ($q) use ($searchString) {
                        $q->where('code', 'like', '%' . $searchString . '%')
                            ->orWhereTranslation('name', 'like', '%' . $searchString . '%');
                    });
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $eventRegistrations = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => EventRegistrationResource::collection($eventRegistrations->items()),
                'total' => $eventRegistrations->total(),
                'perPage' => $eventRegistrations->perPage(),
                'currentPage' => $eventRegistrations->currentPage(),
                'lastPage' => $eventRegistrations->lastPage(),
            ]);

        }
        $eventRegistrations = $query->get();
        return Response::json(EventRegistrationResource::collection($eventRegistrations));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $eventRegistration = EventRegistration::find($id);
            if (!$eventRegistration) {
                App::abort(404);
            }
        } else {
            $eventRegistration = new EventRegistration();
        }

        $validator = Validator::make($request->all(), [
            'event_id' => 'required|integer|exists:events,id',
            'email' => 'required|email',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $eventRegistration->fill($request->all());
            $eventRegistration->save();

            DB::commit();

            if (!$id) {
                EventRegistrationSaved::dispatch($eventRegistration);
            }
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['error' => 'An error occurred while saving the event registration.'], 500);
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

        $eventRegistration = EventRegistration::with(['event'])->find($id);
        if (!$eventRegistration) {
            App::abort(404);
        }

        return Response::json(EventRegistrationResource::make($eventRegistration));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $eventRegistration = EventRegistration::find($id);
        if (!$eventRegistration) {
            App::abort(404);
        }

        $eventRegistration->delete();
        return response()->json();
    }
}
