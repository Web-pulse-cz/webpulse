<?php

namespace App\Http\Controllers\Admin\PersonalGrowth;

use App\Http\Resources\Admin\PersonalGrowth\SeminarResource;
use App\Models\PersonalGrowth\Seminar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SeminarController extends PersonalGrowthController
{
    public function index(Request $request): JsonResponse
    {
        $query = Seminar::query()
            ->where('user_id', $request->user()->id)
            ->withCount(['tasks', 'doneTasks']);

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            // Typ hledáme i podle českého názvu („Školení" → training).
            $needle = mb_strtolower($search);
            $matchingTypes = array_keys(array_filter(
                Seminar::TYPES,
                fn ($label, $key) => str_contains(mb_strtolower($label), $needle) || str_contains($key, $needle),
                ARRAY_FILTER_USE_BOTH
            ));

            $query->where(function ($q) use ($search, $matchingTypes) {
                $q->where('place', 'like', '%'.$search.'%')
                    ->orWhere('organizer', 'like', '%'.$search.'%');
                if ($matchingTypes) {
                    $q->orWhereIn('type', $matchingTypes);
                }
            });
        }

        $type = $request->get('type');
        if ($type && array_key_exists($type, Seminar::TYPES)) {
            $query->where('type', $type);
        }

        $this->applyOrder($query, $request, ['date', 'place', 'organizer', 'type', 'created_at'], 'date');

        if ($request->has('paginate')) {
            return Response::json($this->paginated($request, $query, SeminarResource::class));
        }

        return Response::json(SeminarResource::collection($query->get()));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $seminar = $this->findOwned(Seminar::class, $request, $id);
        $seminar->load('tasks');

        return Response::json(SeminarResource::make($seminar));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        $seminar = $id ? $this->findOwned(Seminar::class, $request, $id) : new Seminar;

        $validator = Validator::make($request->all(), [
            'place' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'organizer' => 'nullable|string|max:255',
            'type' => ['required', Rule::in(array_keys(Seminar::TYPES))],
            'notes' => 'nullable|string',
            'tasks' => 'nullable|array',
            'tasks.*.id' => 'nullable|integer',
            'tasks.*.title' => 'nullable|string|max:500',
            'tasks.*.is_done' => 'boolean',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $seminar->fill($request->only(['place', 'date', 'organizer', 'type', 'notes']));
            $seminar->user_id = $request->user()->id;
            $seminar->save();

            $seminar->syncTasks($request->get('tasks', []) ?? []);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return Response::json(['message' => 'An error occurred while saving seminar.'], 500);
        }

        $seminar->load('tasks');

        return Response::json(SeminarResource::make($seminar));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->findOwned(Seminar::class, $request, $id)->delete();

        return Response::json();
    }
}
