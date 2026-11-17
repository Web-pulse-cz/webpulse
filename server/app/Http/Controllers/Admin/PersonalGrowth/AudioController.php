<?php

namespace App\Http\Controllers\Admin\PersonalGrowth;

use App\Http\Resources\Admin\PersonalGrowth\AudioResource;
use App\Models\PersonalGrowth\Audio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class AudioController extends PersonalGrowthController
{
    public function index(Request $request): JsonResponse
    {
        $query = Audio::query()->where('user_id', $request->user()->id);

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('speaker', 'like', '%'.$search.'%');
                $this->keywordsLike($q, $search);
            });
        }

        $this->applyOrder($query, $request, ['name', 'speaker', 'created_at', 'updated_at'], 'created_at');

        if ($request->has('paginate')) {
            return Response::json($this->paginated($request, $query, AudioResource::class));
        }

        return Response::json(AudioResource::collection($query->get()));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return Response::json(AudioResource::make($this->findOwned(Audio::class, $request, $id)));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        $audio = $id ? $this->findOwned(Audio::class, $request, $id) : new Audio;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'speaker' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'keywords' => 'nullable|array',
            'keywords.*' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $audio->fill($request->only(['name', 'speaker', 'notes']));
        $audio->keywords = $this->normalizeKeywords($request->get('keywords'));
        $audio->user_id = $request->user()->id;
        $audio->save();

        return Response::json(AudioResource::make($audio));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->findOwned(Audio::class, $request, $id)->delete();

        return Response::json();
    }
}
