<?php

namespace App\Http\Controllers\Admin\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Project\ProjectStatusResource;
use App\Models\Project\ProjectStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ProjectStatusController extends Controller
{
	public function index(Request $request): JsonResponse
	{
		$statuses = ProjectStatus::orderBy('position')->get();
		return Response::json(ProjectStatusResource::collection($statuses));
	}

	public function store(Request $request, int $id = null): JsonResponse
	{
		if ($id) {
			$status = ProjectStatus::find($id);
			if (!$status) {
				App::abort(404);
			}
		} else {
			$status = new ProjectStatus();
		}

		$validator = Validator::make($request->all(), [
			'name' => 'required|string|max:255',
			'color' => 'nullable|string|max:20',
		]);

		if ($validator->fails()) {
			return Response::json($validator->errors(), 400);
		}

		try {
			DB::beginTransaction();
			$status->fill($request->all());
			$status->save();
			DB::commit();
		} catch (\Throwable $e) {
			DB::rollBack();
			return Response::json(['message' => 'Chyba při ukládání statusu.'], 500);
		}

		return Response::json(ProjectStatusResource::make($status));
	}

	public function show(int $id): JsonResponse
	{
		$status = ProjectStatus::find($id);
		if (!$status) {
			App::abort(404);
		}

		return Response::json(ProjectStatusResource::make($status));
	}

	public function destroy(int $id): JsonResponse
	{
		$status = ProjectStatus::find($id);
		if (!$status) {
			App::abort(404);
		}

		$status->delete();
		return Response::json();
	}
}
