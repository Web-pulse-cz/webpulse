<?php

namespace App\Http\Controllers\Admin\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Project\ProjectResource;
use App\Http\Resources\Admin\Project\ProjectSimpleResource;
use App\Models\Project\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
	public function index(Request $request): JsonResponse
	{
		$query = Project::with(['client', 'status', 'tags']);

		if ($request->filled('search')) {
			$search = $request->get('search');
			$query->where('name', 'like', '%' . $search . '%');
		}

		if ($request->filled('status_id')) {
			$query->where('status_id', $request->get('status_id'));
		}

		if ($request->filled('client_id')) {
			$query->where('client_id', $request->get('client_id'));
		}

		if ($request->has('is_archived')) {
			$query->where('is_archived', $request->boolean('is_archived'));
		}

		if ($request->has('orderWay') && $request->get('orderBy')) {
			$query->orderBy($request->get('orderBy'), $request->get('orderWay'));
		} else {
			$query->orderBy('created_at', 'desc');
		}

		if ($request->has('paginate')) {
			$projects = $query->paginate($request->get('paginate'));

			return Response::json([
				'data' => ProjectSimpleResource::collection($projects->items()),
				'total' => $projects->total(),
				'perPage' => $projects->perPage(),
				'currentPage' => $projects->currentPage(),
				'lastPage' => $projects->lastPage(),
			]);
		}

		return Response::json(ProjectSimpleResource::collection($query->get()));
	}

	public function store(Request $request, int $id = null): JsonResponse
	{
		if ($id) {
			$project = Project::find($id);
			if (!$project) {
				App::abort(404);
			}
		} else {
			$project = new Project();
		}

		$validator = Validator::make($request->all(), [
			'name' => 'required|string|max:255',
			'client_id' => 'nullable|integer|exists:clients,id',
			'status_id' => 'nullable|integer|exists:project_statuses,id',
			'currency_id' => 'nullable|integer|exists:currencies,id',
			'tax_rate_id' => 'nullable|integer|exists:tax_rates,id',
			'start_date' => 'nullable|date',
			'deadline_date' => 'nullable|date',
			'end_date' => 'nullable|date',
		]);

		if ($validator->fails()) {
			return Response::json($validator->errors(), 400);
		}

		try {
			DB::beginTransaction();

			$project->fill($request->except(['tags']));
			$project->save();

			if ($request->has('tags')) {
				$project->tags()->sync($request->get('tags', []));
			}

			DB::commit();
		} catch (\Throwable $e) {
			DB::rollBack();
			return Response::json(['message' => 'Chyba při ukládání projektu.'], 500);
		}

		return Response::json(ProjectResource::make($project->fresh([
			'client', 'status', 'currency', 'taxRate', 'tags',
			'milestones', 'tasks', 'timeEntries', 'costs', 'notes',
		])));
	}

	public function show(int $id): JsonResponse
	{
		$project = Project::with([
			'client', 'status', 'currency', 'taxRate', 'tags',
			'milestones', 'tasks.user', 'timeEntries.user', 'timeEntries.task',
			'costs', 'notes.user',
		])->find($id);

		if (!$project) {
			App::abort(404);
		}

		return Response::json(ProjectResource::make($project));
	}

	public function destroy(int $id): JsonResponse
	{
		$project = Project::find($id);
		if (!$project) {
			App::abort(404);
		}

		$project->delete();
		return Response::json();
	}
}
