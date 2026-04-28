<?php

namespace App\Http\Controllers\Admin\Reservation;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Reservation\ReservationResource;
use App\Models\Reservation\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Reservation::query()
            ->with(['apartment', 'currency'])
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->filled('apartment_id')) {
            $query->where('apartment_id', $request->get('apartment_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('tab')) {
            $today = now()->toDateString();
            switch ($request->get('tab')) {
                case 'upcoming':
                    $query->where('status', '!=', 'cancelled')
                        ->where('start_date', '>', $today);
                    break;
                case 'current':
                    $query->where('status', 'confirmed')
                        ->where('start_date', '<=', $today)
                        ->where('end_date', '>=', $today);
                    break;
                case 'past':
                    $query->where(function ($q) use ($today) {
                        $q->where('end_date', '<', $today)
                            ->orWhere('status', 'cancelled');
                    });
                    break;
            }
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', '%'.$search.'%')
                    ->orWhere('guest_firstname', 'like', '%'.$search.'%')
                    ->orWhere('guest_lastname', 'like', '%'.$search.'%')
                    ->orWhere('guest_email', 'like', '%'.$search.'%');
            });
        }

        if ($request->has('orderBy') && $request->has('orderWay')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        } else {
            $query->orderBy('start_date', 'desc');
        }

        if ($request->has('paginate')) {
            $items = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => ReservationResource::collection($items->items()),
                'total' => $items->total(),
                'perPage' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
            ]);
        }

        return Response::json(ReservationResource::collection($query->get()));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $item = Reservation::with(['apartment', 'currency'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);

        if (! $item) {
            App::abort(404);
        }

        return Response::json(ReservationResource::make($item));
    }

    public function store(Request $request, ?int $id = null)
    {
        if ($id) {
            $item = Reservation::find($id);
            if (! $item) {
                App::abort(404);
            }
        } else {
            $item = new Reservation;
        }

        $validator = Validator::make($request->all(), [
            'apartment_id' => 'required|exists:apartments,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'nullable|in:pending,confirmed,cancelled,completed',
            'source' => 'nullable|in:admin,web,booking',
            'guest_firstname' => 'required|string|max:255',
            'guest_lastname' => 'required|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:50',
            'number_of_guests' => 'nullable|integer|min:1',
            'total_price' => 'nullable|numeric|min:0',
            'currency_id' => 'nullable|exists:currencies,id',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $item->fill($request->all());
            if (! $id) {
                $item->generateCode();
            }

            $item->save();
            $item->saveSites($item, $request->get('sites', []));

            DB::commit();

            return Response::json(ReservationResource::make($item->fresh(['apartment', 'currency'])));
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['error' => 'An error occurred while saving the reservation.'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $item = Reservation::find($id);
        if (! $item) {
            App::abort(404);
        }

        $item->delete();

        return Response::json();
    }
}
