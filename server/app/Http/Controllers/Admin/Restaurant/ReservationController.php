<?php

namespace App\Http\Controllers\Admin\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Restaurant\ReservationResource;
use App\Models\Restaurant\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Reservation::with(['restaurantTable', 'customer']);

        if ($request->filled('table_id')) {
            $query->where('table_id', $request->get('table_id'));
        }

        if ($request->filled('date')) {
            $query->where('date', $request->get('date'));
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->get('date_to'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('guest_first_name', 'like', '%'.$search.'%')
                    ->orWhere('guest_last_name', 'like', '%'.$search.'%')
                    ->orWhere('guest_phone', 'like', '%'.$search.'%')
                    ->orWhere('guest_email', 'like', '%'.$search.'%');
            });
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        } else {
            $query->orderBy('date', 'desc')->orderBy('time_from', 'desc');
        }

        if ($request->has('paginate')) {
            $reservations = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => ReservationResource::collection($reservations->items()),
                'total' => $reservations->total(),
                'perPage' => $reservations->perPage(),
                'currentPage' => $reservations->currentPage(),
                'lastPage' => $reservations->lastPage(),
            ]);
        }

        return Response::json(ReservationResource::collection($query->get()));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $reservation = Reservation::find($id);
            if (! $reservation) {
                App::abort(404);
            }
        } else {
            $reservation = new Reservation;
        }

        $validator = Validator::make($request->all(), [
            'table_id' => 'required|integer|exists:restaurant_tables,id',
            'date' => 'required|date',
            'time_from' => 'required',
            'guest_first_name' => 'required|string|max:255',
            'guest_last_name' => 'required|string|max:255',
            'guests_count' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        // Check for time conflict
        $conflictQuery = Reservation::where('table_id', $request->get('table_id'))
            ->where('date', $request->get('date'))
            ->where('time_from', $request->get('time_from'))
            ->whereNotIn('status', ['cancelled', 'no_show']);

        if ($id) {
            $conflictQuery->where('id', '!=', $id);
        }

        if ($conflictQuery->exists()) {
            return Response::json(['message' => 'Na tento stůl již existuje rezervace na stejný čas.'], 409);
        }

        try {
            DB::beginTransaction();
            $reservation->fill($request->all());
            $reservation->save();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Reservation save error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return Response::json(['message' => 'Chyba při ukládání rezervace: '.$e->getMessage()], 500);
        }

        return Response::json(ReservationResource::make(
            $reservation->fresh(['restaurantTable', 'customer'])
        ));
    }

    public function show(int $id): JsonResponse
    {
        $reservation = Reservation::with(['restaurantTable', 'customer'])->find($id);
        if (! $reservation) {
            App::abort(404);
        }

        return Response::json(ReservationResource::make($reservation));
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $reservation = Reservation::find($id);
        if (! $reservation) {
            App::abort(404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,seated,completed,cancelled,no_show',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        $reservation->status = $request->get('status');
        $reservation->save();

        return Response::json(ReservationResource::make($reservation->fresh(['restaurantTable', 'customer'])));
    }

    public function destroy(int $id): JsonResponse
    {
        $reservation = Reservation::find($id);
        if (! $reservation) {
            App::abort(404);
        }

        $reservation->delete();

        return Response::json();
    }
}
