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
    public function store(Request $request, string $lang = null): JsonResponse
    {
        $lang = $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:17',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $demand = new Demand();
            $demand->fill($request->all());
            $demand->service_id = $request->get('service_id', null);
            $demand->locale = $lang;

            $demand->save();

            $demand->saveSites($demand, [$siteId]);

            DB::commit();

            DemandSaved::dispatch($demand);
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['error' => 'An error occurred while processing your request.'], 500);
        }

        return Response::json();
    }
}
