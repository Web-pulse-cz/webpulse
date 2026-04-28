<?php

namespace App\Http\Controllers\Admin\Apartment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Apartment\ApartmentBlockResource;
use App\Models\Apartment\ApartmentBlock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ApartmentBlockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ApartmentBlock::query()->with('apartment');

        if ($request->filled('apartment_id')) {
            $query->where('apartment_id', $request->get('apartment_id'));
        }

        $query->orderBy('start_date', 'desc');

        return Response::json(ApartmentBlockResource::collection($query->get()));
    }

    public function show(int $id): JsonResponse
    {
        $item = ApartmentBlock::with('apartment')->find($id);
        if (! $item) {
            App::abort(404);
        }

        return Response::json(ApartmentBlockResource::make($item));
    }

    public function store(Request $request, ?int $id = null)
    {
        if ($id) {
            $item = ApartmentBlock::find($id);
            if (! $item) {
                App::abort(404);
            }
        } else {
            $item = new ApartmentBlock;
        }

        $validator = Validator::make($request->all(), [
            'apartment_id' => 'required|exists:apartments,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|in:maintenance,owner,other',
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $item->fill($request->all());
            $item->save();

            DB::commit();

            return Response::json(ApartmentBlockResource::make($item->fresh('apartment')));
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['error' => 'An error occurred while saving the block.'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $item = ApartmentBlock::find($id);
        if (! $item) {
            App::abort(404);
        }

        $item->delete();

        return Response::json();
    }
}
