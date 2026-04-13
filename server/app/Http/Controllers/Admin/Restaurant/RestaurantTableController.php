<?php

namespace App\Http\Controllers\Admin\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Restaurant\RestaurantTableResource;
use App\Models\Restaurant\RestaurantTable;
use App\Traits\Siteable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class RestaurantTableController extends Controller
{
	use Siteable;

	public function index(Request $request): JsonResponse
	{
		$siteId = $this->handleSite($request->header('X-Site-Hash'));
		$query = RestaurantTable::withCount('upcomingReservations')
			->whereRelation('sites', 'site_id', $siteId);

		if ($request->filled('search')) {
			$search = $request->get('search');
			$query->where(function ($q) use ($search) {
				$q->where('number', 'like', '%' . $search . '%')
					->orWhere('name', 'like', '%' . $search . '%')
					->orWhere('location', 'like', '%' . $search . '%');
			});
		}

		if ($request->filled('status')) {
			$query->where('status', $request->get('status'));
		}

		if ($request->has('orderWay') && $request->get('orderBy')) {
			$query->orderBy($request->get('orderBy'), $request->get('orderWay'));
		} else {
			$query->orderBy('position');
		}

		if ($request->has('paginate')) {
			$tables = $query->paginate($request->get('paginate'));

			return Response::json([
				'data' => RestaurantTableResource::collection($tables->items()),
				'total' => $tables->total(),
				'perPage' => $tables->perPage(),
				'currentPage' => $tables->currentPage(),
				'lastPage' => $tables->lastPage(),
			]);
		}

		return Response::json(RestaurantTableResource::collection($query->get()));
	}

	public function store(Request $request, int $id = null): JsonResponse
	{
		if ($id) {
			$table = RestaurantTable::find($id);
			if (!$table) {
				App::abort(404);
			}
		} else {
			$table = new RestaurantTable();
		}

		$validator = Validator::make($request->all(), [
			'number' => 'required|string|max:50',
			'seats' => 'required|integer|min:1',
		]);

		if ($validator->fails()) {
			return Response::json($validator->errors(), 400);
		}

		try {
			DB::beginTransaction();
			$table->fill($request->except(['sites']));
			$table->save();

			if ($request->has('sites')) {
				$this->saveSites($table, $request->get('sites', []));
			}

			DB::commit();
		} catch (\Throwable $e) {
			DB::rollBack();
			return Response::json(['message' => 'Chyba při ukládání stolu.'], 500);
		}

		return Response::json(RestaurantTableResource::make(
			$table->fresh(['upcomingReservations', 'sites'])
		));
	}

	public function show(Request $request, int $id): JsonResponse
	{
		$siteId = $this->handleSite($request->header('X-Site-Hash'));
		$table = RestaurantTable::with(['upcomingReservations.customer', 'todayReservations.customer', 'reservations.customer', 'sites'])
			->withCount('upcomingReservations')
			->whereRelation('sites', 'site_id', $siteId)
			->find($id);

		if (!$table) {
			App::abort(404);
		}

		// Auto-refresh status
		$table->refreshStatus();

		return Response::json(RestaurantTableResource::make($table));
	}

	public function destroy(int $id): JsonResponse
	{
		$table = RestaurantTable::find($id);
		if (!$table) {
			App::abort(404);
		}

		$table->delete();
		return Response::json();
	}

	public function refreshStatuses(Request $request): JsonResponse
	{
		$siteId = $this->handleSite($request->header('X-Site-Hash'));
		$tables = RestaurantTable::whereRelation('sites', 'site_id', $siteId)->get();

		foreach ($tables as $table) {
			$table->refreshStatus();
		}

		return Response::json(['status' => 'ok']);
	}
}
