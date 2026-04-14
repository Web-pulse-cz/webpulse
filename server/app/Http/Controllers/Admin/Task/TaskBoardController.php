<?php

namespace App\Http\Controllers\Admin\Task;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Task\TaskBoardResource;
use App\Models\Task\TaskBoard;
use App\Traits\Siteable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TaskBoardController extends Controller
{
    use Siteable;

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = TaskBoard::withCount('tasks')
            ->whereRelation('sites', 'site_id', $siteId)
            ->orderBy('position');

        // Optionally load tasks with relations
        if ($request->boolean('with_tasks')) {
            $query->with(['tasks' => function ($q) use ($request, $siteId) {
                $q->where('site_id', $siteId)
                    ->with(['user', 'category', 'assignees', 'project', 'globalBoard'])
                    ->orderBy('position');

                if ($request->filled('project_id')) {
                    $q->where('project_id', $request->get('project_id'));
                }
                if ($request->filled('assignee_id')) {
                    $q->whereHas('assignees', fn ($sq) => $sq->where('users.id', $request->get('assignee_id')));
                }
            }]);
        }

        return Response::json(TaskBoardResource::collection($query->get()));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $board = TaskBoard::find($id);
            if (! $board) {
                App::abort(404);
            }
        } else {
            $board = new TaskBoard;
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
            'is_completed' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();
            $board->fill($request->all());
            if (! $board->color) {
                $board->color = '#6366f1';
            }
            $board->save();

            if ($request->has('sites')) {
                $this->saveSites($board, $request->get('sites', []));
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['message' => 'Chyba při ukládání boardu: '.$e->getMessage()], 500);
        }

        return Response::json(TaskBoardResource::make($board->fresh('sites')));
    }

    public function destroy(int $id): JsonResponse
    {
        $board = TaskBoard::find($id);
        if (! $board) {
            App::abort(404);
        }

        // Unassign tasks instead of deleting
        $board->tasks()->update(['global_board_id' => null]);
        $board->delete();

        return Response::json();
    }
}
