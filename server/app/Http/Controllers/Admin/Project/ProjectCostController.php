<?php

namespace App\Http\Controllers\Admin\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Project\ProjectCostResource;
use App\Models\Project\Project;
use App\Models\Project\ProjectCost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ProjectCostController extends Controller
{
	public function store(Request $request, int $projectId, int $id = null): JsonResponse
	{
		if ($id) {
			$cost = ProjectCost::where('project_id', $projectId)->find($id);
			if (!$cost) {
				App::abort(404);
			}
		} else {
			$cost = new ProjectCost();
			$cost->project_id = $projectId;
		}

		$validator = Validator::make($request->all(), [
			'name' => 'required|string|max:255',
			'amount' => 'required|numeric|min:0',
			'currency_id' => 'nullable|integer|exists:currencies,id',
			'date' => 'nullable|date',
		]);

		if ($validator->fails()) {
			return Response::json($validator->errors(), 400);
		}

		try {
			DB::beginTransaction();
			$cost->fill($request->all());
			$cost->save();

			$this->recalculateProjectCosts($projectId);

			DB::commit();
		} catch (\Throwable $e) {
			DB::rollBack();
			return Response::json(['message' => 'Chyba při ukládání nákladu.'], 500);
		}

		return Response::json(ProjectCostResource::make($cost));
	}

	public function destroy(int $projectId, int $id): JsonResponse
	{
		$cost = ProjectCost::where('project_id', $projectId)->find($id);
		if (!$cost) {
			App::abort(404);
		}

		$cost->delete();
		$this->recalculateProjectCosts($projectId);

		return Response::json();
	}

	protected function recalculateProjectCosts(int $projectId): void
	{
		$project = Project::find($projectId);
		if ($project) {
			$project->total_costs = ProjectCost::where('project_id', $projectId)->sum('amount');
			$project->profit = $project->total_revenue - $project->total_costs;
			$project->saveQuietly();
		}
	}
}
