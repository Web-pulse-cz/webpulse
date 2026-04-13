<?php

namespace App\Http\Controllers\Admin\Shift;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Shift\ShiftResource;
use App\Models\Shift\Shift;
use App\Traits\Siteable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ShiftController extends Controller
{
	use Siteable;

	public function index(Request $request): JsonResponse
	{
		$siteId = $this->handleSite($request->header('X-Site-Hash'));

		$query = Shift::with(['template', 'employees'])
			->whereRelation('sites', 'site_id', $siteId);

		if ($request->filled('date_from')) {
			$query->where('date', '>=', $request->get('date_from'));
		}

		if ($request->filled('date_to')) {
			$query->where('date', '<=', $request->get('date_to'));
		}

		if ($request->filled('employee_id')) {
			$query->whereHas('employees', fn($q) => $q->where('employees.id', $request->get('employee_id')));
		}

		$query->orderBy('date', 'asc')->orderBy('start_time', 'asc');

		return Response::json(ShiftResource::collection($query->get()));
	}

	public function store(Request $request, int $id = null): JsonResponse
	{
		if ($id) {
			$shift = Shift::find($id);
			if (!$shift) {
				App::abort(404);
			}
		} else {
			$shift = new Shift();
		}

		$validator = Validator::make($request->all(), [
			'date' => 'required|date',
			'start_time' => 'required',
			'end_time' => 'required',
			'shift_template_id' => 'nullable|integer|exists:shift_templates,id',
		]);

		if ($validator->fails()) {
			return Response::json($validator->errors(), 400);
		}

		try {
			DB::beginTransaction();

			$shift->fill($request->except(['employees']));
			$shift->save();

			// Sync employees with pivot data
			if ($request->has('employees')) {
				$employeeSync = [];
				foreach ($request->get('employees', []) as $emp) {
					if (is_array($emp)) {
						$employeeSync[$emp['id']] = [
							'status' => $emp['pivot_status'] ?? 'scheduled',
							'note' => $emp['pivot_note'] ?? null,
						];
					} else {
						$employeeSync[$emp] = ['status' => 'scheduled'];
					}
				}
				$shift->employees()->sync($employeeSync);
			}

			if ($request->has('sites')) {
				$this->saveSites($shift, $request->get('sites', []));
			}

			DB::commit();
		} catch (\Throwable $e) {
			DB::rollBack();
			return Response::json(['message' => 'Chyba při ukládání směny.'], 500);
		}

		return Response::json(ShiftResource::make($shift->fresh(['template', 'employees'])));
	}

	public function show(int $id): JsonResponse
	{
		$shift = Shift::with(['template', 'employees'])->find($id);
		if (!$shift) {
			App::abort(404);
		}

		return Response::json(ShiftResource::make($shift));
	}

	public function destroy(int $id): JsonResponse
	{
		$shift = Shift::find($id);
		if (!$shift) {
			App::abort(404);
		}

		$shift->employees()->detach();
		$shift->delete();
		return Response::json();
	}
}
