<?php

namespace App\Http\Controllers\Admin\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Project\ProjectTaskBoardResource;
use App\Models\Project\ProjectTaskBoard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ProjectTaskBoardController extends Controller
{
    public function index(int $projectId): JsonResponse
    {
        $boards = ProjectTaskBoard::where('project_id', $projectId)
            ->with(['tasks' => fn ($q) => $q->with(['user', 'category', 'assignees'])->orderBy('position')])
            ->withCount('tasks')
            ->orderBy('position')
            ->get();

        return Response::json(ProjectTaskBoardResource::collection($boards));
    }

    public function store(Request $request, int $projectId, ?int $id = null): JsonResponse
    {
        if ($id) {
            $board = ProjectTaskBoard::where('project_id', $projectId)->find($id);
            if (! $board) {
                App::abort(404);
            }
        } else {
            $board = new ProjectTaskBoard;
            $board->project_id = $projectId;
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

            // Only one board can be is_completed per project
            if ($board->is_completed) {
                ProjectTaskBoard::where('project_id', $projectId)
                    ->where('id', '!=', $board->id ?? 0)
                    ->update(['is_completed' => false]);
            }

            $board->save();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['message' => 'Chyba při ukládání boardu.'], 500);
        }

        return Response::json(ProjectTaskBoardResource::make($board));
    }

    public function destroy(int $projectId, int $id): JsonResponse
    {
        $board = ProjectTaskBoard::where('project_id', $projectId)->find($id);
        if (! $board) {
            App::abort(404);
        }

        // Move tasks to no board instead of deleting them
        $board->tasks()->update(['board_id' => null]);
        $board->delete();

        return Response::json();
    }
}
