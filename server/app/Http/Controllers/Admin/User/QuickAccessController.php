<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\QickAccess\QuickAccessResource;
use App\Models\QuickAccess\QuickAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class QuickAccessController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $query = QuickAccess::query()
            ->where('user_id', '=', $request->user()->id);

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where(function ($subQuery) use ($searchString) {
                    $subQuery->where('name', 'like', '%' . $searchString . '%')
                        ->orWhere('link', 'like', '%' . $searchString . '%');
                });
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $items = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => QuickAccessResource::collection($items->items()),
                'total' => $items->total(),
                'perPage' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
            ]);
        }

        $items = $query->get();
        return Response::json(QuickAccessResource::collection($items));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $item = QuickAccess::query()
                ->where('id', '=', $id)
                ->where('user_id', '=', $request->user()->id)
                ->first();
            if (!$item) {
                App::abort(404);
            };
        } else {
            $item = new QuickAccess();
        }

        try {
            DB::beginTransaction();

            $item->fill($request->all());
            $item->user_id = $request->user()->id;
            $item->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            App::abort(400, $e->getMessage());
        }

        return Response::json(QuickAccessResource::make($item));
    }

    public function show(Request $request, int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $item = QuickAccess::query()
            ->where('id', '=', $id)
            ->where('user_id', '=', $request->user()->id)
            ->first();

        if (!$item) {
            App::abort(404);
        }

        return Response::json(QuickAccessResource::make($item));
    }

    public function destroy(Request $request, int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $item = QuickAccess::query()
            ->where('id', '=', $id)
            ->where('user_id', '=', $request->user()->id)
            ->first();

        if (!$item) {
            App::abort(404);
        }

        $item->delete();
        return Response::json();
    }
}
