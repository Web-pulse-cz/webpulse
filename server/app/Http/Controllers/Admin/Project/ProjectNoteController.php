<?php

namespace App\Http\Controllers\Admin\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Project\ProjectNoteResource;
use App\Models\Project\ProjectNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ProjectNoteController extends Controller
{
	public function store(Request $request, int $projectId, int $id = null): JsonResponse
	{
		if ($id) {
			$note = ProjectNote::where('project_id', $projectId)->find($id);
			if (!$note) {
				App::abort(404);
			}
		} else {
			$note = new ProjectNote();
			$note->project_id = $projectId;
			$note->user_id = Auth::id();
		}

		$validator = Validator::make($request->all(), [
			'content' => 'required|string',
		]);

		if ($validator->fails()) {
			return Response::json($validator->errors(), 400);
		}

		try {
			DB::beginTransaction();
			$note->fill($request->all());
			$note->save();
			DB::commit();
		} catch (\Throwable $e) {
			DB::rollBack();
			return Response::json(['message' => 'Chyba při ukládání poznámky.'], 500);
		}

		return Response::json(ProjectNoteResource::make($note->fresh('user')));
	}

	public function destroy(int $projectId, int $id): JsonResponse
	{
		$note = ProjectNote::where('project_id', $projectId)->find($id);
		if (!$note) {
			App::abort(404);
		}

		$note->delete();
		return Response::json();
	}
}
