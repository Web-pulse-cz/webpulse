<?php

namespace App\Http\Controllers\Client\Event;

use App\Events\EventRegistrationSaved;
use App\Http\Controllers\Controller;
use App\Models\Event\EventRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class EventRegistrationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $lang): JsonResponse
    {
        $this->handleLanguage($lang);

        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'event_id' => 'required|exists:events,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $eventRegistration = new EventRegistration();
            $eventRegistration->fill($request->all());
            $eventRegistration->save();

            DB::commit();

            EventRegistrationSaved::dispatch($eventRegistration);
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }

        return Response::json();
    }
}
