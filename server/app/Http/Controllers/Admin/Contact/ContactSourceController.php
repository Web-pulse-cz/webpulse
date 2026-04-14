<?php

namespace App\Http\Controllers\Admin\Contact;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Contact\ContactSourceResource;
use App\Models\Contact\ContactSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ContactSourceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ContactSource::query()
            // ->with(['contacts'])
            ->where('user_id', $request->user()->id);

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%'.$searchString[1].'%');
            } else {
                $query->where('name', 'like', '%'.$searchString.'%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        } else {
            $query->orderBy('name', 'asc');
        }

        if ($request->has('paginate')) {
            $contactSources = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => ContactSourceResource::collection($contactSources->items()),
                'total' => $contactSources->total(),
                'perPage' => $contactSources->perPage(),
                'currentPage' => $contactSources->currentPage(),
                'lastPage' => $contactSources->lastPage(),
            ]);
        }

        $contactSources = $query->get();

        return Response::json(ContactSourceResource::collection($contactSources));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $contactSource = ContactSource::find($id);
            if (! $contactSource) {
                App::abort(404);
            }
        } else {
            $contactSource = new ContactSource;
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $contactSource->fill($request->all());
            $contactSource->user_id = $request->user()->id;
            $contactSource->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();

            return Response::json(['message' => 'An error occurred while updating contact source.'], 500);
        }

        return Response::json(ContactSourceResource::make($contactSource));
    }

    public function show(int $id): JsonResponse
    {
        if (! $id) {
            App::abort(400);
        }

        $contactSource = ContactSource::find($id);
        if (! $contactSource) {
            App::abort(404);
        }

        return Response::json(ContactSourceResource::make($contactSource));
    }

    public function destroy(int $id)
    {
        if (! $id) {
            App::abort(400);
        }

        $contactSource = ContactSource::find($id);
        if (! $contactSource) {
            App::abort(404);
        }

        $contactSource->delete();

        return Response::json();
    }
}
