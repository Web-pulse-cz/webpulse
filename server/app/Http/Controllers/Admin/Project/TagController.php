<?php

namespace App\Http\Controllers\Admin\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Project\TagResource;
use App\Models\Project\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return Response::json(TagResource::collection(Tag::orderBy('name')->get()));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $tag = Tag::find($id);
            if (! $tag) {
                App::abort(404);
            }
        } else {
            $tag = new Tag;
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();
            $tag->fill($request->all());
            $tag->save();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['message' => 'Chyba při ukládání tagu.'], 500);
        }

        return Response::json(TagResource::make($tag));
    }

    public function show(int $id): JsonResponse
    {
        $tag = Tag::find($id);
        if (! $tag) {
            App::abort(404);
        }

        return Response::json(TagResource::make($tag));
    }

    public function destroy(int $id): JsonResponse
    {
        $tag = Tag::find($id);
        if (! $tag) {
            App::abort(404);
        }

        $tag->delete();

        return Response::json();
    }
}
