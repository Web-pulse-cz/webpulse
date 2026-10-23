<?php

namespace App\Http\Controllers\Admin\League;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\League\LeagueActivityResource;
use App\Http\Resources\Admin\League\LeagueSectionResource;
use App\Models\League\League;
use App\Models\League\LeagueActivity;
use App\Models\League\LeagueEntry;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

/**
 * League detail for participants — recording activities and leaderboard.
 */
class LeagueBoardController extends Controller
{
    /**
     * Visible leagues with the current user's total and the current leader.
     */
    public function overview(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));
        $user = $request->user();

        $leagues = League::query()
            ->withCount('activities')
            ->whereRelation('sites', 'site_id', $siteId)
            ->visibleFor($user)
            ->orderBy('position')
            ->get();

        return Response::json($leagues->map(function (League $league) use ($user) {
            $leaderboard = $this->leaderboard($league);
            $me = collect($leaderboard)->firstWhere('user_id', $user->id);

            return [
                'id' => $league->id,
                'name' => $league->name,
                'description' => $league->description,
                'scoring_type' => $league->scoring_type,
                'visibility' => $league->visibility,
                'activities_count' => $league->activities_count,
                'participants_count' => count($leaderboard),
                'leader' => $leaderboard[0] ?? null,
                'my_total' => $me['total'] ?? 0,
                'my_rank' => $me['rank'] ?? null,
            ];
        })->values());
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $league = $this->findLeague($request, $id);
        $user = $request->user();

        $league->load(['sections.activities', 'activities']);

        [$from, $to] = $this->recordRange();

        $myEntries = LeagueEntry::query()
            ->where('league_id', $league->id)
            ->where('user_id', $user->id)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->get(['league_activity_id', 'date', 'count']);

        $days = [];
        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            $dayEntries = $myEntries->filter(fn ($e) => $e->date->toDateString() === $date->toDateString());
            $days[] = [
                'date' => $date->toDateString(),
                'total' => $this->sumEntries($league, $dayEntries),
                'entries' => $dayEntries->mapWithKeys(fn ($e) => [$e->league_activity_id => $e->count]),
            ];
        }

        $recent = LeagueEntry::query()
            ->with(['user' => fn ($q) => $q->withOnly([]), 'activity'])
            ->where('league_id', $league->id)
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get()
            ->map(fn (LeagueEntry $e) => [
                'id' => $e->id,
                'date' => $e->date->toDateString(),
                'count' => $e->count,
                'user_id' => $e->user_id,
                'user_name' => $e->user ? trim($e->user->firstname.' '.$e->user->lastname) : null,
                'user_avatar' => $e->user?->avatar,
                'activity_name' => $e->activity?->name,
                'value' => (int) ($e->activity?->value ?? 0) * $e->count,
                'counts_to_league' => (bool) $e->activity?->counts_to_league,
                'updated_at' => $e->updated_at,
            ]);

        return Response::json([
            'league' => [
                'id' => $league->id,
                'name' => $league->name,
                'description' => $league->description,
                'scoring_type' => $league->scoring_type,
                'visibility' => $league->visibility,
                'sections' => LeagueSectionResource::collection($league->sections),
                'activities' => LeagueActivityResource::collection($league->activities->whereNull('league_section_id')->values()),
            ],
            'days' => $days,
            'leaderboard' => $this->leaderboard($league),
            'recent' => $recent,
            'me' => $user->id,
        ]);
    }

    /**
     * Set the count of an activity for the current user on a given day (0 removes the entry).
     */
    public function record(Request $request, int $id): JsonResponse
    {
        $league = $this->findLeague($request, $id);
        [$from, $to] = $this->recordRange();

        $validator = Validator::make($request->all(), [
            'league_activity_id' => 'required|integer',
            'date' => 'required|date_format:Y-m-d|after_or_equal:'.$from->toDateString().'|before_or_equal:'.$to->toDateString(),
            'count' => 'required|integer|min:0',
        ], [
            'date.after_or_equal' => 'Záznam lze provést maximálně '.League::RECORD_DAYS_BACK.' dní zpět.',
            'date.before_or_equal' => 'Nelze zaznamenat aktivitu do budoucna.',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        $activity = LeagueActivity::where('league_id', $league->id)->find($request->get('league_activity_id'));
        if (! $activity) {
            App::abort(404);
        }

        $count = (int) $request->get('count');
        if ($activity->max_per_day !== null && $count > $activity->max_per_day) {
            return Response::json(['count' => ['Aktivitu lze zaznamenat maximálně '.$activity->max_per_day.'× denně.']], 400);
        }

        $attributes = [
            'league_activity_id' => $activity->id,
            'user_id' => $request->user()->id,
            'date' => $request->get('date'),
        ];

        if ($count === 0) {
            LeagueEntry::where($attributes)->delete();
        } else {
            LeagueEntry::updateOrCreate($attributes, ['league_id' => $league->id, 'count' => $count]);
        }

        return $this->show($request, $league->id);
    }

    private function findLeague(Request $request, int $id): League
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $league = League::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->visibleFor($request->user())
            ->find($id);

        if (! $league) {
            App::abort(404);
        }

        return $league;
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function recordRange(): array
    {
        $to = Carbon::today();

        return [$to->copy()->subDays(League::RECORD_DAYS_BACK), $to];
    }

    private function sumEntries(League $league, $entries): int
    {
        $activities = $league->relationLoaded('activities')
            ? $league->activities->keyBy('id')
            : $league->activities()->get()->keyBy('id');

        return (int) $entries->sum(function ($e) use ($activities) {
            $activity = $activities->get($e->league_activity_id);

            return $activity && $activity->counts_to_league ? $activity->value * $e->count : 0;
        });
    }

    /**
     * Ranked participants. Only activities flagged counts_to_league add to totals.
     * Participants = anyone with an entry + creator and invited members of invite-only leagues.
     */
    private function leaderboard(League $league): array
    {
        $weekFrom = Carbon::today()->subDays(6)->toDateString();

        $totals = DB::table('league_entries as e')
            ->join('league_activities as a', 'a.id', '=', 'e.league_activity_id')
            ->where('e.league_id', $league->id)
            ->groupBy('e.user_id')
            ->select([
                'e.user_id',
                DB::raw('SUM(CASE WHEN a.counts_to_league = 1 THEN e.count * a.value ELSE 0 END) as total'),
                DB::raw("SUM(CASE WHEN a.counts_to_league = 1 AND e.date >= '{$weekFrom}' THEN e.count * a.value ELSE 0 END) as week_total"),
                DB::raw('SUM(e.count) as records'),
                DB::raw('COUNT(DISTINCT e.date) as active_days'),
            ])
            ->get()
            ->keyBy('user_id');

        $userIds = $totals->keys()->all();
        if ($league->visibility === League::VISIBILITY_INVITE) {
            $userIds = array_merge($userIds, $league->members()->pluck('users.id')->all());
            if ($league->created_by) {
                $userIds[] = $league->created_by;
            }
        }

        $users = User::query()
            ->withOnly([])
            ->whereIn('id', array_unique($userIds))
            ->get(['id', 'firstname', 'lastname', 'avatar']);

        $rows = $users->map(function (User $u) use ($totals) {
            $t = $totals->get($u->id);

            return [
                'user_id' => $u->id,
                'name' => trim($u->firstname.' '.$u->lastname),
                'avatar' => $u->avatar,
                'total' => (int) ($t->total ?? 0),
                'week_total' => (int) ($t->week_total ?? 0),
                'records' => (int) ($t->records ?? 0),
                'active_days' => (int) ($t->active_days ?? 0),
            ];
        })->sortBy([['total', 'desc'], ['name', 'asc']])->values();

        // Dense ranking — same total, same rank
        $rank = 0;
        $prev = null;

        return $rows->map(function ($row) use (&$rank, &$prev) {
            if ($prev === null || $row['total'] !== $prev) {
                $rank++;
                $prev = $row['total'];
            }

            return $row + ['rank' => $rank];
        })->all();
    }
}
