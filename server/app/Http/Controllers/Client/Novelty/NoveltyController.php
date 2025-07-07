<?php

namespace App\Http\Controllers\Client\Novelty;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Novelty\NoveltyResource;
use App\Models\Novelty\Novelty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class NoveltyController extends Controller
{
    public function index(Request $request, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);

        $query = Novelty::query()
            ->where('active', true)
            ->orderBy('priority', 'asc');

        if ($request->has('paginate')) {
            $novelties = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => NoveltyResource::collection($novelties->items()),
                'total' => $novelties->total(),
                'perPage' => $novelties->perPage(),
                'currentPage' => $novelties->currentPage(),
                'lastPage' => $novelties->lastPage(),
            ]);
        }

        $novelties = $query->get();

        return Response::json(NoveltyResource::collection($novelties));

    }

    public function show(Request $request, int $id, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);

        if (!$id) {
            return Response::json(['message' => 'Novelty ID is required'], 400);
        }

        $novelty = Novelty::query()
            ->where('active', true)
            ->find($id);

        if (!$novelty) {
            return Response::json(['message' => 'Novelty not found'], 404);
        }

        return Response::json(NoveltyResource::make($novelty));
    }
}
