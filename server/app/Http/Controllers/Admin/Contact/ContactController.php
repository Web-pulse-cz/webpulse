<?php

namespace App\Http\Controllers\Admin\Contact;

use App\Events\ContactUpdatedEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Contact\ContactSimpleResource;
use App\Models\Contact\Contact;
use App\Models\Contact\ContactHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $query = Contact::query()
            ->with(['contact'])
            ->where('user_id', $request->user()->id);

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where('firstname', 'like', '%' . $searchString . '%')
                    ->orWhere('lastname', 'like', '%' . $searchString . '%')
                    ->orWhere('phone', 'like', '%' . $searchString . '%')
                    ->orWhere('email', 'like', '%' . $searchString . '%')
                    ->orWhere('company', 'like', '%' . $searchString . '%')
                    ->orWhere('street', 'like', '%' . $searchString . '%')
                    ->orWhere('city', 'like', '%' . $searchString . '%')
                    ->orWhere('zip', 'like', '%' . $searchString . '%')
                    ->orWhere('occupation', 'like', '%' . $searchString . '%')
                    ->orWhere('goal', 'like', '%' . $searchString . '%');
            }
        }
        if ($request->has('filters')) {
            $rawFilters = json_decode($request->get('filters'), true);
            if (!empty($rawFilters)) {
                foreach ($rawFilters as $key => $rawFilter) {
                    switch ($key) {
                        case 'phase':
                            $query->whereIn('contact_phase_id', $rawFilter);
                            break;
                        case 'source':
                            $query->whereIn('contact_source_id', $rawFilter);
                            break;
                        case 'contact':
                            $query->where('contact_id', $rawFilter);
                            break;
                    }
                }
            }
        }

        if($request->has('contact_list_id') && $request->get('contact_list_id') != null) {
            $query->whereHas('lists', function ($q) use ($request) {
                $q->where('contact_list_id', $request->get('contact_list_id'));
            });
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $contacts = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => ContactSimpleResource::collection($contacts->items()),
                'total' => $contacts->total(),
                'perPage' => $contacts->perPage(),
                'currentPage' => $contacts->currentPage(),
                'lastPage' => $contacts->lastPage(),
            ]);
        }

        $contacts = $query->get();
        return Response::json(ContactSimpleResource::collection($contacts));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $contact = Contact::find($id);
            if (!$contact) {
                App::abort(404);
            }
        } else {
            $contact = new Contact();
        }

        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'goal' => 'nullable|string|max:255',
            'contact_phase_id' => 'nullable|integer|exists:contact_phases,id',
            'contact_source_id' => 'nullable|integer|exists:contact_sources,id',
            'contact_id' => 'nullable|integer|exists:contacts,id',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();
            $oldContact = null;
            if ($id) {
                $oldContact = $contact;
            }

            $contact->fill($request->all());
            $contact->user_id = $request->user()->id;

            if ($request->has('formatted_last_contacted_at') && $request->get('formatted_last_contacted_at') != null) {
                $contact->last_contacted_at = Carbon::parse($request->get('formatted_last_contacted_at'));
            } else {
                $contact->last_contacted_at = null;
            }

            if ($request->has('formatted_next_meeting') && $request->get('formatted_next_meeting') != null) {
                $contact->next_meeting = Carbon::parse($request->get('formatted_next_meeting'));
            } else {
                $contact->next_meeting = null;
            }

            if ($request->has('formatted_next_contact') && $request->get('formatted_next_contact') != null) {
                $contact->next_contact = Carbon::parse($request->get('formatted_next_contact'));
            } else {
                $contact->next_contact = null;
            }

            if ($id) {
                $contact->syncTasks($request);
                $contact->syncLists($request);
            }

            if ($request->has('contact_id')) {
                $contact->contact_id = $request->contact_id ?? null;
            }
            $contact->save();

            if ($id) {
                ContactUpdatedEvent::dispatch($oldContact, $contact);
            } else {
                $history = new ContactHistory();
                $history->fill([
                    'name' => 'Kontakt vytvořen',
                    'description' => 'Vytvořili jste nový kontakt!',
                    'origin' => 'system',
                    'type' => 'other',
                ]);
                $history->contact()->associate($contact);
                $history->save();
            }

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating contact.'], 500);
        }

        return Response::json(\App\Http\Resources\Admin\Contact\ContactResource::make($contact));
    }

    public function show(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $contact = Contact::with(['contact', 'source', 'contacts', 'phase', 'tasks', 'histories' => function ($query) {
            return $query->orderBy('created_at', 'desc');
        }])->find($id);
        if (!$contact) {
            App::abort(404);
        }

        return Response::json(\App\Http\Resources\Admin\Contact\ContactResource::make($contact));
    }

    public function destroy(int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $contact = Contact::find($id);
        if (!$contact) {
            App::abort(404);
        }

        $contact->delete();
        return Response::json();
    }

    public function history(Request $request, int $id, int $historyId = null): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $contact = Contact::find($id);
        if (!$contact) {
            App::abort(404);
        }

        if ($historyId) {
            $history = ContactHistory::find($historyId);
            if (!$history) {
                App::abort(404);
            }
        } else {
            $history = new ContactHistory();
        }

        try {
            DB::beginTransaction();

            $history->fill($request->all());
            $history->contact()->associate($contact);

            if ($request->has('activity_id')) {
                $history->activity_id = $request->get('activity_id');
            }

            /*if ($request->has('contact_phase_id')) {
                $history->contact_phase_id = $request->get('contact_phase_id');
            }*/

            if (!$request->has('description') || $request->get('description') == null) {
                $history->description = 'Nepřidali jste žádnou poznámku.';
            }
            $history->origin = 'user';

            $history->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating contact history.'], 500);
        }

        return Response::json([]);
    }

    public function historyDestroy(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $history = ContactHistory::find($id);
        if (!$history) {
            App::abort(404);
        }

        $history->delete();
        return Response::json([]);
    }
}
