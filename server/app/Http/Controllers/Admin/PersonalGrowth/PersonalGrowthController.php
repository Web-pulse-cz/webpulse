<?php

namespace App\Http\Controllers\Admin\PersonalGrowth;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * Společný základ pro Osobní růst — vše je per-user (user_id přihlášeného uživatele).
 */
abstract class PersonalGrowthController extends Controller
{
    /**
     * @template T of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<T>  $modelClass
     * @return T
     */
    protected function findOwned(string $modelClass, Request $request, int $id)
    {
        $model = $modelClass::query()
            ->where('user_id', $request->user()->id)
            ->find($id);

        if (! $model) {
            App::abort(404);
        }

        return $model;
    }

    protected function applyOrder(Builder $query, Request $request, array $allowed, string $default, string $defaultWay = 'desc'): void
    {
        $orderBy = $request->get('orderBy');
        $orderWay = strtolower((string) $request->get('orderWay')) === 'asc' ? 'asc' : 'desc';

        if ($orderBy && in_array($orderBy, $allowed, true)) {
            $query->orderBy($orderBy, $orderWay);
        } else {
            $query->orderBy($default, $defaultWay);
        }

        $query->orderBy('id', 'desc');
    }

    /** Case-insensitive hledání v JSON poli klíčových slov. */
    protected function keywordsLike(Builder $query, string $search): void
    {
        $query->orWhereRaw(
            'LOWER(CAST(keywords AS CHAR CHARACTER SET utf8mb4)) LIKE ?',
            ['%'.mb_strtolower($search).'%']
        );
    }

    /** @return array<int, string> */
    protected function normalizeKeywords(mixed $keywords): array
    {
        if (! is_array($keywords)) {
            return [];
        }

        $result = [];
        foreach ($keywords as $keyword) {
            $keyword = trim((string) $keyword);
            if ($keyword !== '' && ! in_array(mb_strtolower($keyword), array_map('mb_strtolower', $result), true)) {
                $result[] = mb_substr($keyword, 0, 100);
            }
        }

        return $result;
    }

    protected function paginated(Request $request, Builder $query, string $resourceClass)
    {
        $items = $query->paginate((int) $request->get('paginate', 25));

        return [
            'data' => $resourceClass::collection($items->items()),
            'total' => $items->total(),
            'perPage' => $items->perPage(),
            'currentPage' => $items->currentPage(),
            'lastPage' => $items->lastPage(),
        ];
    }
}
