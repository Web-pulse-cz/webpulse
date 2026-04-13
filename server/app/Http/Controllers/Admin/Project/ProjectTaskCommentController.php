<?php

namespace App\Http\Controllers\Admin\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Project\ProjectTaskCommentResource;
use App\Models\Project\ProjectTaskComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ProjectTaskCommentController extends Controller
{
    public function store(Request $request, int $projectId, int $taskId, ?int $id = null): JsonResponse
    {
        if ($id) {
            $comment = ProjectTaskComment::where('task_id', $taskId)->find($id);
            if (! $comment) {
                App::abort(404);
            }
        } else {
            $comment = new ProjectTaskComment;
            $comment->task_id = $taskId;
            $comment->user_id = Auth::id();
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();
            $comment->fill($request->all());
            $comment->save();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['message' => 'Chyba při ukládání komentáře.'], 500);
        }

        return Response::json(ProjectTaskCommentResource::make($comment->fresh('user')));
    }

    public function destroy(int $projectId, int $taskId, int $id): JsonResponse
    {
        $comment = ProjectTaskComment::where('task_id', $taskId)->find($id);
        if (! $comment) {
            App::abort(404);
        }

        $comment->delete();

        return Response::json();
    }
}
