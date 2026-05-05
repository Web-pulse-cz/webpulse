<?php

namespace App\Http\Controllers\Client\Demand;

use App\Events\DemandSaved;
use App\Http\Controllers\Controller;
use App\Models\Demand\Demand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class DemandController extends Controller
{
    public function store(Request $request, ?string $lang = null): JsonResponse
    {
        $lang = $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:17',
            'message' => 'nullable|string',
            'text' => 'nullable|string',
            'service_id' => 'nullable|integer',
            'offer_price' => 'nullable|numeric',
            'url' => 'nullable|string|max:2048',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        // Body of the demand may arrive as either `message` (frontend convention)
        // or `text` (DB column name). Require at least one of them.
        $body = trim((string) $request->input('message', $request->input('text', '')));
        if ($body === '') {
            return Response::json([
                'errors' => ['message' => ['Pole zpráva je povinné.']],
            ], 422);
        }

        DB::beginTransaction();
        try {
            $demand = new Demand;
            $demand->fill([
                'fullname' => $request->input('fullname'),
                'email' => $request->input('email'),
                'phone' => (string) $request->input('phone', ''),
                'phone_prefix' => $request->input('phone_prefix'),
                'url' => $request->input('url'),
                'text' => $body,
                'offer_price' => $request->input('offer_price'),
            ]);
            $demand->service_id = $request->get('service_id', null);
            $demand->locale = $lang;

            $demand->save();

            $demand->saveSites($demand, [$siteId]);

            DB::commit();

            DemandSaved::dispatch($demand);
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();

            return Response::json([
                'error' => 'An error occurred while processing your request.',
                'detail' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }

        return Response::json();
    }
}
