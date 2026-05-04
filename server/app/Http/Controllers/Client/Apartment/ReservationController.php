<?php

namespace App\Http\Controllers\Client\Apartment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Apartment\ReservationResource;
use App\Models\Apartment\Apartment;
use App\Models\Reservation\Reservation;
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
            'apartment_id' => 'required|exists:apartments,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'guest_firstname' => 'required|string|max:255',
            'guest_lastname' => 'required|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:50',
            'number_of_guests' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $apartment = Apartment::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($request->get('apartment_id'));

        if (! $apartment) {
            return Response::json(['error' => 'Apartment not available on this site.'], 404);
        }

        $conflict = Reservation::query()
            ->where('apartment_id', $apartment->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->get('start_date'), $request->get('end_date')])
                    ->orWhereBetween('end_date', [$request->get('start_date'), $request->get('end_date')])
                    ->orWhere(function ($q2) use ($request) {
                        $q2->where('start_date', '<=', $request->get('start_date'))
                            ->where('end_date', '>=', $request->get('end_date'));
                    });
            })
            ->exists();

        if ($conflict) {
            return Response::json(['error' => 'Selected dates are not available.'], 409);
        }

        DB::beginTransaction();
        try {
            $reservation = new Reservation;
            $reservation->fill($request->only([
                'apartment_id',
                'start_date',
                'end_date',
                'guest_firstname',
                'guest_lastname',
                'guest_email',
                'guest_phone',
                'number_of_guests',
                'notes',
            ]));
            $reservation->status = 'pending';
            $reservation->source = 'web';
            $reservation->generateCode();
            $reservation->save();
            $reservation->saveSites($reservation, [$siteId]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['error' => 'An error occurred while creating the reservation.'], 500);
        }

        return Response::json(ReservationResource::make($reservation), 201);
    }
}
