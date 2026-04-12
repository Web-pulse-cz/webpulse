<?php

namespace App\Http\Controllers\Admin\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Project\ProjectTaskCategoryResource;
use App\Models\Project\ProjectTaskCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ProjectTaskCategoryController extends Controller
{
	public function index(int $projectId): JsonResponse
	{
		$categories = ProjectTaskCategory::where('project_id', $projectId)
			->withCount('tasks')
			->orderBy('position')
			->get();

		return Response::json(ProjectTaskCategoryResource::collection($categories));
	}

	public function store(Request $request, int $projectId, int $id = null): JsonResponse
	{
		if ($id) {
			$category = ProjectTaskCategory::where('project_id', $projectId)->find($id);
			if (!$category) {
				App::abort(404);
			}
		} else {
			$category = new ProjectTaskCategory();
			$category->project_id = $projectId;
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
			$category->fill($request->all());
			$category->save();
			DB::commit();
		} catch (\Throwable $e) {
			DB::rollBack();
			return Response::json(['message' => 'Chyba při ukládání kategorie.'], 500);
		}

		return Response::json(ProjectTaskCategoryResource::make($category));
	}

	public function destroy(int $projectId, int $id): JsonResponse
	{
		$category = ProjectTaskCategory::where('project_id', $projectId)->find($id);
		if (!$category) {
			App::abort(404);
		}

		$category->delete();
		return Response::json();
	}
}
