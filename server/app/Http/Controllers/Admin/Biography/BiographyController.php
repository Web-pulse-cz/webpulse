<?php

namespace App\Http\Controllers\Admin\Biography;

use App\Events\BiographySaved;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Biography\BiographyResource;
use App\Http\Resources\Admin\Biography\BiographySimpleResource;
use App\Models\Biography\Biography;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BiographyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Biography::query()
            ->where('user_id', $request->user()->id);

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where('name', 'like', '%' . $searchString . '%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $biographies = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => BiographySimpleResource::collection($biographies->items()),
                'total' => $biographies->total(),
                'perPage' => $biographies->perPage(),
                'currentPage' => $biographies->currentPage(),
                'lastPage' => $biographies->lastPage(),
            ]);
        }

        $biographies = $query->get();
        return Response::json(BiographySimpleResource::collection($biographies));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $biography = Biography::find($id);
            if (!$biography) {
                App::abort(404);
            }
        } else {
            $biography = new Biography();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'template' => 'nullable|string|max:255',
            'phone_prefix' => 'nullable|string|max:10',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'linkedin' => 'nullable|url|max:255',
            'github' => 'nullable|url|max:255',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'about_me' => 'nullable|string',
            'summary' => 'nullable|string',
            'job_title' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $biography->fill($request->all());
            $biography->template = 'default';
            $biography->phone_prefix = '+420';
            $biography->user_id = $request->user()->id;

            $biography->save();
            //BiographySaved::dispatch($biography);

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating biography.'], 500);
        }

        return Response::json(BiographyResource::make($biography));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $biography = Biography::where('user_id', $request->user()->id)
            ->find($id);

        if (!$biography) {
            App::abort(404);
        }

        return Response::json(BiographyResource::make($biography));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $biography = Biography::find($id)
            ->where('user_id', $request->user()->id);

        if (!$biography) {
            App::abort(404);
        }

        // todo unlink file from storage
        $biography->delete();
        return Response::json();
    }

    public function download(int $id)
    {
        //
    }
}
