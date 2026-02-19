<?php

namespace App\Http\Controllers\Admin\Message;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Message\MessageBlueprintResource;
use App\Models\Message\MessageBlueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class MessageBlueprintController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $query = MessageBlueprint::query()
            ->where('user_id', $request->user()->id);

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where(function ($subQuery) use ($searchString) {
                    $subQuery->where('name', 'like', '%' . $searchString . '%')
                        ->orWhere('type', 'like', '%' . $searchString . '%');
                });
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $messageBlueprints = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => MessageBlueprintResource::collection($messageBlueprints->items()),
                'total' => $messageBlueprints->total(),
                'perPage' => $messageBlueprints->perPage(),
                'currentPage' => $messageBlueprints->currentPage(),
                'lastPage' => $messageBlueprints->lastPage(),
            ]);
        }

        $messageBlueprints = $query->get();
        return Response::json(\App\Http\Resources\Admin\Message\MessageBlueprintResource::collection($messageBlueprints));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $messageBlueprint = MessageBlueprint::find($id);
            if (!$messageBlueprint) {
                App::abort(404);
            }
        } else {
            $messageBlueprint = new MessageBlueprint();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $messageBlueprint->fill($request->all());
            $messageBlueprint->user_id = $request->user()->id;
            $messageBlueprint->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating message blueprint.'], 500);
        }

        return Response::json(\App\Http\Resources\Admin\Message\MessageBlueprintResource::make($messageBlueprint));
    }

    public function show(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $messageBlueprint = MessageBlueprint::find($id);
        if (!$messageBlueprint) {
            App::abort(404);
        }

        return Response::json(MessageBlueprintResource::make($messageBlueprint));
    }

    public function destroy(int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $messageBlueprint = MessageBlueprint::find($id);
        if (!$messageBlueprint) {
            App::abort(404);
        }

        $messageBlueprint->delete();
        return Response::json();
    }
}
