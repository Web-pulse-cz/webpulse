<?php

namespace App\Http\Controllers\Admin\Filemanager;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Filemanager\FilemanagerResource;
use App\Models\Filemanager\Filemanager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class FilemanagerController extends Controller
{
    private const ALLOWED_MODES = ['cover', 'contain', 'stretch'];

    private const ALLOWED_POSITIONS = [
        'top-left', 'top', 'top-right',
        'left', 'center', 'right',
        'bottom-left', 'bottom', 'bottom-right',
    ];

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Filemanager::query()
            ->with('sites')
            ->whereRelation('sites', 'sites.id', $siteId)
            ->orderBy('entity_type')
            ->orderBy('position');

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->get('entity_type'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('entity_type', 'like', '%'.$search.'%')
                    ->orWhere('format', 'like', '%'.$search.'%')
                    ->orWhere('path', 'like', '%'.$search.'%');
            });
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->reorder()->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $rows = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => FilemanagerResource::collection($rows->items()),
                'total' => $rows->total(),
                'perPage' => $rows->perPage(),
                'currentPage' => $rows->currentPage(),
                'lastPage' => $rows->lastPage(),
            ]);
        }

        return Response::json(FilemanagerResource::collection($query->get()));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        if (! $id) {
            App::abort(400);
        }

        $filemanager = Filemanager::with('sites')->find($id);
        if (! $filemanager) {
            App::abort(404);
        }

        return Response::json(FilemanagerResource::make($filemanager));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'entity_type' => 'required|string|max:64',
            'format' => 'required|string|max:64',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'mode' => 'required|string|in:'.implode(',', self::ALLOWED_MODES),
            'crop_position' => 'required|string|in:'.implode(',', self::ALLOWED_POSITIONS),
            'path' => 'required|string|max:128',
            'position' => 'nullable|integer|min:0',
            'sites' => 'array',
            'sites.*' => 'integer|exists:sites,id',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        if ($id) {
            $filemanager = Filemanager::find($id);
            if (! $filemanager) {
                App::abort(404);
            }
        } else {
            $filemanager = new Filemanager;
        }

        DB::beginTransaction();
        try {
            $filemanager->fill($request->only([
                'entity_type', 'format', 'width', 'height',
                'mode', 'crop_position', 'path', 'position',
            ]));
            $filemanager->save();

            $filemanager->sites()->sync($request->get('sites', []));

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['message' => 'An error occurred while saving filemanager.'], 500);
        }

        $filemanager->load('sites');

        return Response::json(FilemanagerResource::make($filemanager));
    }

    public function destroy(int $id): JsonResponse
    {
        if (! $id) {
            App::abort(400);
        }

        $filemanager = Filemanager::find($id);
        if (! $filemanager) {
            App::abort(404);
        }

        $filemanager->delete();

        return Response::json();
    }
}
