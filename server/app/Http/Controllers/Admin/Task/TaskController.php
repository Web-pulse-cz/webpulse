<?php

namespace App\Http\Controllers\Admin\Task;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Project\ProjectTaskResource;
use App\Models\Project\ProjectTask;
use App\Models\Task\TaskBoard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
	public function index(Request $request): JsonResponse
	{
		$siteId = $this->handleSite($request->header('X-Site-Hash'));

		$query = ProjectTask::where('site_id', $siteId)
			->with(['user', 'category', 'board', 'globalBoard', 'assignees', 'project']);

		if ($request->filled('project_id')) {
			$query->where('project_id', $request->get('project_id'));
		}

		if ($request->filled('global_board_id')) {
			$query->where('global_board_id', $request->get('global_board_id'));
		}

		if ($request->filled('assignee_id')) {
			$query->whereHas('assignees', fn($q) => $q->where('users.id', $request->get('assignee_id')));
		}

		if ($request->filled('priority')) {
			$query->where('priority', $request->get('priority'));
		}

		if ($request->filled('search')) {
			$search = $request->get('search');
			$query->where(function ($q) use ($search) {
				$q->where('name', 'like', '%' . $search . '%')
					->orWhere('code', 'like', '%' . $search . '%');
			});
		}

		$query->orderBy('position');

		if ($request->has('paginate')) {
			$tasks = $query->paginate($request->get('paginate'));

			return Response::json([
				'data' => ProjectTaskResource::collection($tasks->items()),
				'total' => $tasks->total(),
				'perPage' => $tasks->perPage(),
				'currentPage' => $tasks->currentPage(),
				'lastPage' => $tasks->lastPage(),
			]);
		}

		return Response::json(ProjectTaskResource::collection($query->get()));
	}

	public function show(int $id): JsonResponse
	{
		$task = ProjectTask::with([
			'user', 'category', 'board', 'globalBoard', 'assignees',
			'project', 'comments.user', 'timeEntries.user',
		])
			->withCount('comments')
			->find($id);

		if (!$task) {
			App::abort(404);
		}

		return Response::json(ProjectTaskResource::make($task));
	}

	public function store(Request $request, int $id = null): JsonResponse
	{
		$siteId = $this->handleSite($request->header('X-Site-Hash'));

		if ($id) {
			$task = ProjectTask::find($id);
			if (!$task) {
				App::abort(404);
			}
		} else {
			$task = new ProjectTask();
			$task->site_id = $siteId;
			$task->user_id = Auth::id();
		}

		$validator = Validator::make($request->all(), [
			'name' => 'required|string|max:255',
			'project_id' => 'nullable|integer|exists:projects,id',
			'global_board_id' => 'nullable|integer|exists:task_boards,id',
			'category_id' => 'nullable|integer|exists:project_task_categories,id',
			'due_date' => 'nullable|date',
			'estimated_hours' => 'nullable|numeric|min:0',
		]);

		if ($validator->fails()) {
			return Response::json($validator->errors(), 400);
		}

		try {
			DB::beginTransaction();

			$task->fill($request->except(['assignees']));

			// Handle completed_at based on global board
			if ($task->global_board_id) {
				$board = TaskBoard::find($task->global_board_id);
				if ($board?->is_completed && !$task->completed_at) {
					$task->completed_at = now();
				} elseif (!$board?->is_completed) {
					$task->completed_at = null;
				}
			}

			$task->save();

			if ($request->has('assignees')) {
				$task->assignees()->sync($request->get('assignees', []));
			}

			DB::commit();
		} catch (\Throwable $e) {
			DB::rollBack();
			return Response::json(['message' => 'Chyba při ukládání úkolu: ' . $e->getMessage()], 500);
		}

		return Response::json(ProjectTaskResource::make(
			$task->fresh(['user', 'category', 'board', 'globalBoard', 'assignees', 'project'])
		));
	}

	public function move(Request $request, int $id): JsonResponse
	{
		$task = ProjectTask::find($id);
		if (!$task) {
			App::abort(404);
		}

		try {
			DB::beginTransaction();

			if ($request->has('global_board_id')) {
				$task->global_board_id = $request->get('global_board_id');

				$board = TaskBoard::find($task->global_board_id);
				if ($board?->is_completed && !$task->completed_at) {
					$task->completed_at = now();
				} elseif (!$board?->is_completed) {
					$task->completed_at = null;
				}
			}

			if ($request->has('position')) {
				$task->position = $request->get('position');
			}

			$task->save();
			DB::commit();
		} catch (\Throwable $e) {
			DB::rollBack();
			return Response::json(['message' => 'Chyba při přesunu úkolu.'], 500);
		}

		return Response::json(ProjectTaskResource::make(
			$task->fresh(['user', 'category', 'board', 'globalBoard', 'assignees', 'project'])
		));
	}

	public function destroy(int $id): JsonResponse
	{
		$task = ProjectTask::find($id);
		if (!$task) {
			App::abort(404);
		}

		$task->delete();
		return Response::json();
	}
}
