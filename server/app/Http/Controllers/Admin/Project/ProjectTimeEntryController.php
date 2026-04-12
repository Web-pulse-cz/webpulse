<?php

namespace App\Http\Controllers\Admin\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Project\ProjectTimeEntryResource;
use App\Models\Project\Project;
use App\Models\Project\ProjectTimeEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ProjectTimeEntryController extends Controller
{
	public function store(Request $request, int $projectId, int $id = null): JsonResponse
	{
		if ($id) {
			$entry = ProjectTimeEntry::where('project_id', $projectId)->find($id);
			if (!$entry) {
				App::abort(404);
			}
		} else {
			$entry = new ProjectTimeEntry();
			$entry->project_id = $projectId;
			$entry->user_id = Auth::id();
		}

		$validator = Validator::make($request->all(), [
			'task_id' => 'nullable|integer|exists:project_tasks,id',
			'hours' => 'nullable|numeric|min:0',
			'hourly_rate' => 'nullable|numeric|min:0',
			'date' => 'nullable|date',
		]);

		if ($validator->fails()) {
			return Response::json($validator->errors(), 400);
		}

		try {
			DB::beginTransaction();
			$entry->fill($request->except(['is_running', 'timer_started_at']));
			if (!$entry->date) {
				$entry->date = now()->toDateString();
			}
			$entry->save();

			$this->recalculateProjectHours($projectId);

			DB::commit();
		} catch (\Throwable $e) {
			DB::rollBack();
			return Response::json(['message' => 'Chyba při ukládání záznamu.'], 500);
		}

		return Response::json(ProjectTimeEntryResource::make($entry->fresh(['user', 'task'])));
	}

	public function destroy(int $projectId, int $id): JsonResponse
	{
		$entry = ProjectTimeEntry::where('project_id', $projectId)->find($id);
		if (!$entry) {
			App::abort(404);
		}

		$entry->delete();
		$this->recalculateProjectHours($projectId);

		return Response::json();
	}

	public function startTimer(int $projectId): JsonResponse
	{
		// Stop any currently running timer for this user
		ProjectTimeEntry::where('user_id', Auth::id())
			->where('is_running', true)
			->each(function ($entry) {
				$this->stopRunningEntry($entry);
			});

		$entry = new ProjectTimeEntry();
		$entry->project_id = $projectId;
		$entry->user_id = Auth::id();
		$entry->date = now()->toDateString();
		$entry->hours = 0;
		$entry->timer_started_at = now();
		$entry->is_running = true;

		$project = Project::find($projectId);
		if ($project) {
			$entry->hourly_rate = $project->hourly_rate;
		}

		$entry->save();

		return Response::json(ProjectTimeEntryResource::make($entry->fresh(['user', 'task'])));
	}

	public function stopTimer(int $projectId, int $id): JsonResponse
	{
		$entry = ProjectTimeEntry::where('project_id', $projectId)->find($id);
		if (!$entry || !$entry->is_running) {
			App::abort(404);
		}

		$this->stopRunningEntry($entry);
		$this->recalculateProjectHours($projectId);

		return Response::json(ProjectTimeEntryResource::make($entry->fresh(['user', 'task'])));
	}

	protected function stopRunningEntry(ProjectTimeEntry $entry): void
	{
		if ($entry->timer_started_at) {
			$elapsed = Carbon::parse($entry->timer_started_at)->diffInSeconds(now());
			$entry->hours = round($entry->hours + ($elapsed / 3600), 2);
		}
		$entry->is_running = false;
		$entry->timer_started_at = null;
		$entry->save();
	}

	protected function recalculateProjectHours(int $projectId): void
	{
		$project = Project::find($projectId);
		if ($project) {
			$project->total_tracked_hours = ProjectTimeEntry::where('project_id', $projectId)->sum('hours');
			$project->total_revenue = $project->total_tracked_hours * $project->hourly_rate;
			$project->profit = $project->total_revenue - $project->total_costs;
			$project->saveQuietly();
		}
	}
}
