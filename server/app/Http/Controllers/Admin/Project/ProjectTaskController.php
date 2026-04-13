<?php

namespace App\Http\Controllers\Admin\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Project\ProjectTaskResource;
use App\Models\Project\ProjectTask;
use App\Models\Project\ProjectTaskBoard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ProjectTaskController extends Controller
{
    public function index(Request $request, int $projectId): JsonResponse
    {
        $query = ProjectTask::where('project_id', $projectId)
            ->with(['user', 'category', 'board', 'assignees']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        if ($request->filled('board_id')) {
            $query->where('board_id', $request->get('board_id'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->get('priority'));
        }

        if ($request->filled('assignee_id')) {
            $query->whereHas('assignees', fn ($q) => $q->where('users.id', $request->get('assignee_id')));
        }

        $tasks = $query->orderBy('position')->get();

        return Response::json(ProjectTaskResource::collection($tasks));
    }

    public function show(int $projectId, int $id): JsonResponse
    {
        $task = ProjectTask::where('project_id', $projectId)
            ->with(['user', 'category', 'board', 'assignees', 'comments.user', 'timeEntries.user'])
            ->withCount('comments')
            ->find($id);

        if (! $task) {
            App::abort(404);
        }

        return Response::json(ProjectTaskResource::make($task));
    }

    public function store(Request $request, int $projectId, ?int $id = null): JsonResponse
    {
        if ($id) {
            $task = ProjectTask::where('project_id', $projectId)->find($id);
            if (! $task) {
                App::abort(404);
            }
        } else {
            $task = new ProjectTask;
            $task->project_id = $projectId;
            $task->user_id = Auth::id();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|integer|exists:project_task_categories,id',
            'board_id' => 'nullable|integer|exists:project_task_boards,id',
            'milestone_id' => 'nullable|integer|exists:project_milestones,id',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $task->fill($request->except(['assignees']));

            // Handle completed_at based on board
            if ($task->board_id) {
                $board = ProjectTaskBoard::find($task->board_id);
                if ($board?->is_completed && ! $task->completed_at) {
                    $task->completed_at = now();
                } elseif (! $board?->is_completed) {
                    $task->completed_at = null;
                }
            }

            $task->save();

            // Sync assignees
            if ($request->has('assignees')) {
                $task->assignees()->sync($request->get('assignees', []));
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['message' => 'Chyba při ukládání úkolu.'], 500);
        }

        return Response::json(ProjectTaskResource::make(
            $task->fresh(['user', 'category', 'board', 'assignees'])
        ));
    }

    public function move(Request $request, int $projectId, int $id): JsonResponse
    {
        $task = ProjectTask::where('project_id', $projectId)->find($id);
        if (! $task) {
            App::abort(404);
        }

        $validator = Validator::make($request->all(), [
            'board_id' => 'nullable|integer|exists:project_task_boards,id',
            'category_id' => 'nullable|integer|exists:project_task_categories,id',
            'position' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            if ($request->has('board_id')) {
                $task->board_id = $request->get('board_id');

                $board = ProjectTaskBoard::find($task->board_id);
                if ($board?->is_completed && ! $task->completed_at) {
                    $task->completed_at = now();
                } elseif (! $board?->is_completed) {
                    $task->completed_at = null;
                }
            }

            if ($request->has('category_id')) {
                $task->category_id = $request->get('category_id');
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
            $task->fresh(['user', 'category', 'board', 'assignees'])
        ));
    }

    public function reorder(Request $request, int $projectId): JsonResponse
    {
        $items = $request->get('items', []);

        try {
            DB::beginTransaction();
            foreach ($items as $item) {
                ProjectTask::where('project_id', $projectId)
                    ->where('id', $item['id'])
                    ->update([
                        'position' => $item['position'] ?? 0,
                        'board_id' => $item['board_id'] ?? null,
                    ]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['message' => 'Chyba při řazení úkolů.'], 500);
        }

        return Response::json(['status' => 'ok']);
    }

    public function destroy(int $projectId, int $id): JsonResponse
    {
        $task = ProjectTask::where('project_id', $projectId)->find($id);
        if (! $task) {
            App::abort(404);
        }

        $task->delete();

        return Response::json();
    }
}
