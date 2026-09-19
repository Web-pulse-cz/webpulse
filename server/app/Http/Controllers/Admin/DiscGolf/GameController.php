<?php

namespace App\Http\Controllers\Admin\DiscGolf;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\DiscGolf\GameResource;
use App\Models\DiscGolf\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class GameController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Game::query()
            ->with(['course', 'layout', 'players.player'])
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('custom_course_name', 'like', '%'.$search.'%')
                    ->orWhereHas('course', fn ($c) => $c->where('name', 'like', '%'.$search.'%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->get('course_id'));
        }

        if ($request->has('orderBy') && $request->has('orderWay')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        } else {
            $query->orderBy('played_at', 'desc');
        }

        if ($request->has('paginate')) {
            $items = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => GameResource::collection($items->items()),
                'total' => $items->total(),
                'perPage' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
            ]);
        }

        return Response::json(GameResource::collection($query->get()));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $item = Game::with(['course', 'layout', 'holes', 'players.player', 'players.scores'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);

        if (! $item) {
            App::abort(404);
        }

        return Response::json(GameResource::make($item));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $item = Game::find($id);
        if (! $item) {
            App::abort(404);
        }

        if ($request->has('count_to_cup')) {
            $item->count_to_cup = $request->boolean('count_to_cup');
        }

        $item->save();

        return Response::json(GameResource::make($item));
    }

    public function destroy(int $id): JsonResponse
    {
        $item = Game::find($id);
        if (! $item) {
            App::abort(404);
        }

        $item->delete();

        return Response::json();
    }

    /**
     * Wide matrix view: one row per completed game, per-player Par / Handicap / Celkem cells.
     */
    public function matrix(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $games = Game::query()
            ->with(['course', 'layout', 'players.player'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->where('status', 'completed')
            ->where('count_to_cup', true)
            ->orderBy('played_at', 'desc')
            ->get();

        $rows = $games->map(function (Game $game) {
            $players = $game->players
                ->filter(fn ($gp) => $gp->player && $gp->player->include_in_stats)
                ->map(fn ($gp) => [
                    'player_id' => $gp->player_id,
                    'player_name' => $gp->player?->name,
                    'handicap' => $gp->handicap,
                    'total_throws' => $gp->total_throws,
                    'net_score' => $gp->net_score,
                    'relative_to_par' => $gp->relative_to_par,
                ])
                ->values();

            $winner = $game->winner;

            return [
                'id' => $game->id,
                'played_at' => $game->played_at,
                'course_name' => $game->course_name,
                'layout_name' => $game->layout_name,
                'par' => $game->par,
                'note' => $game->note,
                'winner' => $winner ? [
                    'player_id' => $winner->player_id,
                    'player_name' => $winner->player?->name,
                    'net_score' => $winner->net_score,
                    'relative_to_par' => $winner->relative_to_par,
                ] : null,
                'players' => $players,
            ];
        });

        return Response::json(['data' => $rows]);
    }

    /**
     * Gellerův pohár standings: per player, sum of relative-to-par (net score − par)
     * across all games flagged count_to_cup — lower total is better.
     */
    public function cup(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $games = Game::query()
            ->with('players.player')
            ->whereRelation('sites', 'site_id', $siteId)
            ->where('status', 'completed')
            ->where('count_to_cup', true)
            ->get();

        $standings = [];

        foreach ($games as $game) {
            foreach ($game->players as $gamePlayer) {
                if (! $gamePlayer->player || ! $gamePlayer->player->include_in_stats) {
                    continue;
                }

                $playerId = $gamePlayer->player_id;

                if (! isset($standings[$playerId])) {
                    $standings[$playerId] = [
                        'player_id' => $playerId,
                        'player_name' => $gamePlayer->player->name,
                        'games_played' => 0,
                        'total_relative_to_par' => 0,
                    ];
                }

                $standings[$playerId]['games_played']++;
                $standings[$playerId]['total_relative_to_par'] += $gamePlayer->relative_to_par ?? 0;
            }
        }

        $rows = collect($standings)
            ->map(function ($row) {
                $row['average_relative_to_par'] = $row['games_played'] > 0
                    ? round($row['total_relative_to_par'] / $row['games_played'], 2)
                    : null;

                return $row;
            })
            ->sortBy('total_relative_to_par')
            ->values();

        return Response::json(['data' => $rows]);
    }
}
