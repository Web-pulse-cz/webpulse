<?php

namespace App\Http\Controllers\Admin\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Project\ProjectMilestoneResource;
use App\Models\Project\ProjectMilestone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ProjectMilestoneController extends Controller
{
    public function store(Request $request, int $projectId, ?int $id = null): JsonResponse
    {
        if ($id) {
            $milestone = ProjectMilestone::where('project_id', $projectId)->find($id);
            if (! $milestone) {
                App::abort(404);
            }
        } else {
            $milestone = new ProjectMilestone;
            $milestone->project_id = $projectId;
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();
            $milestone->fill($request->all());
            $milestone->save();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['message' => 'Chyba při ukládání milníku.'], 500);
        }

        return Response::json(ProjectMilestoneResource::make($milestone));
    }

    public function destroy(int $projectId, int $id): JsonResponse
    {
        $milestone = ProjectMilestone::where('project_id', $projectId)->find($id);
        if (! $milestone) {
            App::abort(404);
        }

        $milestone->delete();

        return Response::json();
    }

    public function complete(int $projectId, int $id): JsonResponse
    {
        $milestone = ProjectMilestone::where('project_id', $projectId)->find($id);
        if (! $milestone) {
            App::abort(404);
        }

        $milestone->completed_at = $milestone->completed_at ? null : now();
        $milestone->save();

        return Response::json(ProjectMilestoneResource::make($milestone));
    }
}
