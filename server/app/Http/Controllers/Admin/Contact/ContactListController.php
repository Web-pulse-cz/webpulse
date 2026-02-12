<?php

namespace App\Http\Controllers\Admin\Contact;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Contact\ContactListResource;
use App\Models\Contact\ContactList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ContactListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ContactList::query()
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
            $contactList = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => ContactListResource::collection($contactList->items()),
                'total' => $contactList->total(),
                'perPage' => $contactList->perPage(),
                'currentPage' => $contactList->currentPage(),
                'lastPage' => $contactList->lastPage(),
            ]);
        }

        $contactList = $query->get();
        return Response::json(ContactListResource::collection($contactList));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $contactList = ContactList::query()
            ->where('user_id', $request->user()->id)->find($id);
            if (!$contactList) {
                App::abort(404);
            }
        } else {
            $contactList = new ContactList();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $contactList->fill($request->all());
            $contactList->user_id = $request->user()->id;

            $contactList->save();

            DB::commit();
        } catch (\Throwable | \Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating contact list.'], 500);
        }

        return Response::json(ContactListResource::make($contactList));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $contact_list = ContactList::query()
            ->where('user_id', $request->user()->id)
            ->find($id);

        if (!$contact_list) {
            App::abort(404);
        }

        return Response::json(ContactListResource::make($contact_list));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $contact_list = ContactList::query()
            ->where('user_id', $request->user()->id)
            ->find($id);

        if (!$contact_list) {
            App::abort(404);
        }

        $contact_list->delete();
        return Response::json();
    }
}
