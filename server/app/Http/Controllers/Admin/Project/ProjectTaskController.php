<?php

namespace App\Http\Controllers\Admin\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Project\ProjectTaskResource;
use App\Models\Project\ProjectTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ProjectTaskController extends Controller
{
	public function store(Request $request, int $projectId, int $id = null): JsonResponse
	{
		if ($id) {
			$task = ProjectTask::where('project_id', $projectId)->find($id);
			if (!$task) {
				App::abort(404);
			}
		} else {
			$task = new ProjectTask();
			$task->project_id = $projectId;
		}

		$validator = Validator::make($request->all(), [
			'name' => 'required|string|max:255',
			'milestone_id' => 'nullable|integer|exists:project_milestones,id',
			'user_id' => 'nullable|integer|exists:users,id',
			'due_date' => 'nullable|date',
		]);

		if ($validator->fails()) {
			return Response::json($validator->errors(), 400);
		}

		try {
			DB::beginTransaction();
			$task->fill($request->all());

			if ($request->get('status') === 'completed' && !$task->completed_at) {
				$task->completed_at = now();
			} elseif ($request->get('status') !== 'completed') {
				$task->completed_at = null;
			}

			$task->save();
			DB::commit();
		} catch (\Throwable $e) {
			DB::rollBack();
			return Response::json(['message' => 'Chyba při ukládání úkolu.'], 500);
		}

		return Response::json(ProjectTaskResource::make($task->fresh('user')));
	}

	public function destroy(int $projectId, int $id): JsonResponse
	{
		$task = ProjectTask::where('project_id', $projectId)->find($id);
		if (!$task) {
			App::abort(404);
		}

		$task->delete();
		return Response::json();
	}
}
