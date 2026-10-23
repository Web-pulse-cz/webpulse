<?php

namespace App\Http\Controllers\Admin\League;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\League\LeagueResource;
use App\Models\League\League;
use App\Models\League\LeagueActivity;
use App\Models\League\LeagueSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LeagueController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = League::query()
            ->with(['creator' => fn ($q) => $q->withOnly([])])
            ->withCount(['activities', 'members'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->visibleFor($request->user());

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->get('search').'%');
        }

        if ($request->has('orderBy') && $request->has('orderWay')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        } else {
            $query->orderBy('position');
        }

        if ($request->has('paginate')) {
            $items = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => LeagueResource::collection($items->items()),
                'total' => $items->total(),
                'perPage' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
            ]);
        }

        return Response::json(LeagueResource::collection($query->get()));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $item = League::query()
            ->with($this->detailRelations())
            ->whereRelation('sites', 'site_id', $siteId)
            ->visibleFor($request->user())
            ->find($id);

        if (! $item) {
            App::abort(404);
        }

        return Response::json(LeagueResource::make($item));
    }

    public function store(Request $request, ?int $id = null)
    {
        if ($id) {
            $item = League::visibleFor($request->user())->find($id);
            if (! $item) {
                App::abort(404);
            }
        } else {
            $item = new League;
            $item->created_by = $request->user()->id;
            $item->position = (int) League::max('position') + 1;
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scoring_type' => ['required', Rule::in([League::SCORING_POINTS, League::SCORING_MINUTES])],
            'visibility' => ['required', Rule::in([League::VISIBILITY_PUBLIC, League::VISIBILITY_INVITE])],
            'members' => 'nullable|array',
            'members.*' => 'integer|exists:users,id',
            'sections' => 'nullable|array',
            'sections.*.name' => 'required|string|max:255',
            'sections.*.activities' => 'nullable|array',
            'activities' => 'nullable|array',
            'sections.*.activities.*.name' => 'required|string|max:255',
            'sections.*.activities.*.value' => 'required|integer|min:0',
            'sections.*.activities.*.max_per_day' => 'nullable|integer|min:1',
            'activities.*.name' => 'required|string|max:255',
            'activities.*.value' => 'required|integer|min:0',
            'activities.*.max_per_day' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $item->fill($request->only(['name', 'description', 'scoring_type', 'visibility', 'position']));
            $item->save();

            $item->saveSites($item, $request->get('sites', []));
            $item->members()->sync($request->get('members', []));

            $this->syncStructure($item, $request->get('sections', []), $request->get('activities', []));

            DB::commit();

            return Response::json(LeagueResource::make($item->fresh($this->detailRelations())));
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['error' => 'An error occurred while saving the league.'], 500);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $item = League::visibleFor($request->user())->find($id);
        if (! $item) {
            App::abort(404);
        }

        $item->delete();

        return Response::json();
    }

    public function reorder(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $allowedIds = League::whereRelation('sites', 'site_id', $siteId)->pluck('id')->toArray();

        DB::transaction(function () use ($request, $allowedIds) {
            foreach ($request->get('ids', []) as $index => $id) {
                if (! in_array((int) $id, $allowedIds, true)) {
                    continue;
                }
                League::where('id', $id)->update(['position' => $index]);
            }
        });

        return Response::json();
    }

    private function detailRelations(): array
    {
        return [
            'sections.activities' => fn ($q) => $q->withCount('entries'),
            'activities' => fn ($q) => $q->withCount('entries'),
            'members' => fn ($q) => $q->withOnly([]),
            'creator' => fn ($q) => $q->withOnly([]),
        ];
    }

    /**
     * Upsert sections with their activities and unsectioned activities; delete removed ones.
     * Order in the payload arrays defines the position.
     */
    private function syncStructure(League $league, array $sections, array $activities): void
    {
        $keptSectionIds = [];
        $keptActivityIds = [];

        foreach ($sections as $index => $sectionData) {
            $section = isset($sectionData['id'])
                ? LeagueSection::where('league_id', $league->id)->find($sectionData['id'])
                : null;

            if (! $section) {
                $section = new LeagueSection;
                $section->league_id = $league->id;
            }

            $section->name = $sectionData['name'];
            $section->position = $index;
            $section->save();

            $keptSectionIds[] = $section->id;

            foreach ($sectionData['activities'] ?? [] as $activityIndex => $activityData) {
                $keptActivityIds[] = $this->saveActivity($league, $section->id, $activityIndex, $activityData);
            }
        }

        foreach ($activities as $activityIndex => $activityData) {
            $keptActivityIds[] = $this->saveActivity($league, null, $activityIndex, $activityData);
        }

        LeagueActivity::where('league_id', $league->id)->whereNotIn('id', $keptActivityIds)->delete();
        LeagueSection::where('league_id', $league->id)->whereNotIn('id', $keptSectionIds)->delete();
    }

    private function saveActivity(League $league, ?int $sectionId, int $position, array $data): int
    {
        $activity = isset($data['id'])
            ? LeagueActivity::where('league_id', $league->id)->find($data['id'])
            : null;

        if (! $activity) {
            $activity = new LeagueActivity;
            $activity->league_id = $league->id;
        }

        $activity->league_section_id = $sectionId;
        $activity->name = $data['name'];
        $activity->value = (int) ($data['value'] ?? 0);
        $activity->max_per_day = isset($data['max_per_day']) && $data['max_per_day'] !== '' ? (int) $data['max_per_day'] : null;
        $activity->counts_to_league = (bool) ($data['counts_to_league'] ?? true);
        $activity->position = $position;
        $activity->save();

        return $activity->id;
    }
}
