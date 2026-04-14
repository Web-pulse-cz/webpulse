<?php

namespace App\Http\Controllers\Admin\Contact;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Contact\ContactTaskResource;
use App\Models\Contact\ContactTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ContactTaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ContactTask::query()
            ->with(['contacts'])
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
            $query->orderBy('contact_phase_id', 'asc');
        }

        if ($request->has('paginate')) {
            $contactTasks = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => ContactTaskResource::collection($contactTasks->items()),
                'total' => $contactTasks->total(),
                'perPage' => $contactTasks->perPage(),
                'currentPage' => $contactTasks->currentPage(),
                'lastPage' => $contactTasks->lastPage(),
            ]);
        }

        $contactTasks = $query->get();

        return Response::json(ContactTaskResource::collection($contactTasks));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $contactTask = ContactTask::find($id);
            if (! $contactTask) {
                App::abort(404);
            }
        } else {
            $contactTask = new ContactTask;
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phase_id' => 'nullable|integer|exists:contact_phases,id',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $contactTask->fill($request->all());
            $contactTask->user_id = $request->user()->id;

            $contactTask->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();

            return Response::json(['message' => 'An error occurred while updating contact task.'], 500);
        }

        return Response::json(ContactTaskResource::make($contactTask));
    }

    public function show(int $id): JsonResponse
    {
        if (! $id) {
            App::abort(400);
        }

        $contactTask = ContactTask::find($id);
        if (! $contactTask) {
            App::abort(404);
        }

        return Response::json(ContactTaskResource::make($contactTask));
    }

    public function destroy(int $id)
    {
        if (! $id) {
            App::abort(400);
        }

        $contactTask = ContactTask::find($id);
        if (! $contactTask) {
            App::abort(404);
        }

        $contactTask->delete();

        return Response::json();
    }
}
