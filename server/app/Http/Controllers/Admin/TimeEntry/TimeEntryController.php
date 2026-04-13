<?php

namespace App\Http\Controllers\Admin\TimeEntry;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Project\ProjectTimeEntryResource;
use App\Models\Project\Project;
use App\Models\Project\ProjectTimeEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TimeEntryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ProjectTimeEntry::with(['project.currency', 'project.taxRate', 'task', 'user']);

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->get('date_to'));
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->get('project_id'));
        }

        if ($request->filled('task_id')) {
            $query->where('task_id', $request->get('task_id'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->get('site_id'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('description', 'like', '%'.$search.'%');
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        } else {
            $query->orderBy('date', 'desc')->orderBy('created_at', 'desc');
        }

        if ($request->has('paginate')) {
            $entries = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => ProjectTimeEntryResource::collection($entries->items()),
                'total' => $entries->total(),
                'perPage' => $entries->perPage(),
                'currentPage' => $entries->currentPage(),
                'lastPage' => $entries->lastPage(),
                'total_hours' => ProjectTimeEntry::query()
                    ->when($request->filled('date_from'), fn ($q) => $q->where('date', '>=', $request->get('date_from')))
                    ->when($request->filled('date_to'), fn ($q) => $q->where('date', '<=', $request->get('date_to')))
                    ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->get('project_id')))
                    ->when($request->filled('task_id'), fn ($q) => $q->where('task_id', $request->get('task_id')))
                    ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->get('user_id')))
                    ->sum('seconds'),
            ]);
        }

        return Response::json(ProjectTimeEntryResource::collection($query->get()));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $entry = ProjectTimeEntry::find($id);
            if (! $entry) {
                App::abort(404);
            }
        } else {
            $entry = new ProjectTimeEntry;
            $entry->user_id = Auth::id();
        }

        $validator = Validator::make($request->all(), [
            'project_id' => 'nullable|integer|exists:projects,id',
            'task_id' => 'nullable|integer|exists:project_tasks,id',
            'hours' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();
            $entry->fill($request->except(['is_running', 'timer_started_at']));
            if (! $entry->date) {
                $entry->date = now()->toDateString();
            }
            $entry->site_id = $this->handleSite($request->header('X-Site-Hash'));
            $entry->save();

            $this->recalculateProjectHours($entry->project_id);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['message' => 'Chyba při ukládání záznamu.'], 500);
        }

        return Response::json(ProjectTimeEntryResource::make($entry->fresh(['project', 'task', 'user'])));
    }

    public function destroy(int $id): JsonResponse
    {
        $entry = ProjectTimeEntry::find($id);
        if (! $entry) {
            App::abort(404);
        }

        $projectId = $entry->project_id;
        $entry->delete();
        $this->recalculateProjectHours($projectId);

        return Response::json();
    }

    public function getRunning(): JsonResponse
    {
        $entry = ProjectTimeEntry::where('user_id', Auth::id())
            ->where('is_running', true)
            ->with(['project', 'task', 'user'])
            ->first();

        if (! $entry) {
            return Response::json(null);
        }

        return Response::json(ProjectTimeEntryResource::make($entry));
    }

    public function startTimer(Request $request): JsonResponse
    {
        // Stop any currently running timer
        ProjectTimeEntry::where('user_id', Auth::id())
            ->where('is_running', true)
            ->each(function ($entry) {
                $this->stopRunningEntry($entry);
            });

        $entry = new ProjectTimeEntry;
        $entry->project_id = $request->get('project_id');
        $entry->task_id = $request->get('task_id');
        $entry->user_id = Auth::id();
        $entry->description = $request->get('description', '');
        $entry->date = now()->toDateString();
        $entry->seconds = 0;
        $entry->timer_started_at = now();
        $entry->is_running = true;
        $entry->site_id = $this->handleSite($request->header('X-Site-Hash'));

        if ($entry->project_id) {
            $project = Project::find($entry->project_id);
            $entry->hourly_rate = $project?->hourly_rate ?? 0;
        }

        $entry->save();

        return Response::json(ProjectTimeEntryResource::make($entry->fresh(['project', 'task', 'user'])));
    }

    public function stopTimer(int $id): JsonResponse
    {
        $entry = ProjectTimeEntry::find($id);
        if (! $entry || ! $entry->is_running) {
            App::abort(404);
        }

        $this->stopRunningEntry($entry);
        $this->recalculateProjectHours($entry->project_id);

        return Response::json(ProjectTimeEntryResource::make($entry->fresh(['project', 'task', 'user'])));
    }

    public function exportPdf(Request $request)
    {
        $query = ProjectTimeEntry::with(['project.currency', 'project.taxRate', 'task', 'user']);

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->get('date_to'));
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->get('project_id'));
        }
        if ($request->filled('task_id')) {
            $query->where('task_id', $request->get('task_id'));
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }
        if ($request->filled('search')) {
            $query->where('description', 'like', '%'.$request->get('search').'%');
        }

        $entries = $query->orderBy('date', 'desc')->get();
        $totalSeconds = $entries->sum('seconds');
        $totalHours = $totalSeconds / 3600;
        $totalCost = $entries->sum(fn ($e) => ($e->seconds / 3600) * ($e->hourly_rate ?? 0));

        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'project' => $request->filled('project_id') ? Project::find($request->get('project_id'))?->name : null,
        ];

        $pdf = Pdf::loadView('pdf.time-entries', [
            'entries' => $entries,
            'totalSeconds' => $totalSeconds,
            'totalHours' => $totalHours,
            'totalCost' => $totalCost,
            'filters' => $filters,
        ]);

        return $pdf->download('casove-zaznamy-'.now()->format('Y-m-d').'.pdf');
    }

    protected function stopRunningEntry(ProjectTimeEntry $entry): void
    {
        if ($entry->timer_started_at) {
            $elapsed = (int) Carbon::parse($entry->timer_started_at)->diffInSeconds(now());
            $entry->seconds = $entry->seconds + $elapsed;
        }
        $entry->is_running = false;
        $entry->timer_started_at = null;
        $entry->save();
    }

    protected function recalculateProjectHours(?int $projectId): void
    {
        if (! $projectId) {
            return;
        }

        $project = Project::find($projectId);
        if ($project) {
            $project->total_tracked_seconds = (int) ProjectTimeEntry::where('project_id', $projectId)->sum('seconds');
            $totalHours = $project->total_tracked_seconds / 3600;
            $project->total_revenue = $totalHours * $project->hourly_rate;
            $project->profit = $project->total_revenue - $project->total_costs;
            $project->saveQuietly();
        }
    }
}
