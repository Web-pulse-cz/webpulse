<?php

namespace App\Http\Controllers\Client\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Restaurant\ReservationResource;
use App\Models\Restaurant\Reservation;
use App\Models\Restaurant\RestaurantTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    public function store(Request $request, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $validator = Validator::make($request->all(), [
            'table_id' => 'required|integer|exists:restaurant_tables,id',
            'date' => 'required|date|after_or_equal:today',
            'time_from' => 'required|date_format:H:i',
            'time_to' => 'nullable|date_format:H:i|after:time_from',
            'guest_first_name' => 'required|string|max:255',
            'guest_last_name' => 'required|string|max:255',
            'guest_phone_prefix' => 'nullable|string|max:10',
            'guest_phone' => 'nullable|string|max:50',
            'guest_email' => 'nullable|email|max:255',
            'guests_count' => 'required|integer|min:1',
            'note' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $table = RestaurantTable::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($request->get('table_id'));

        if (! $table) {
            return Response::json(['error' => 'Table not available on this site.'], 404);
        }

        if ((int) $request->get('guests_count') > $table->seats) {
            return Response::json(['error' => 'Number of guests exceeds table capacity.'], 422);
        }

        $conflict = Reservation::query()
            ->where('table_id', $table->id)
            ->where('date', $request->get('date'))
            ->where('time_from', $request->get('time_from'))
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->exists();

        if ($conflict) {
            return Response::json(['error' => 'Na tento stůl již existuje rezervace na stejný čas.'], 409);
        }

        DB::beginTransaction();
        try {
            $reservation = new Reservation;
            $reservation->fill($request->only([
                'table_id',
                'date',
                'time_from',
                'time_to',
                'guest_first_name',
                'guest_last_name',
                'guest_phone_prefix',
                'guest_phone',
                'guest_email',
                'guests_count',
                'note',
            ]));
            $reservation->status = 'pending';
            $reservation->source = 'web';
            $reservation->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['error' => 'An error occurred while creating the reservation.'], 500);
        }

        return Response::json(ReservationResource::make($reservation), 201);
    }
}
