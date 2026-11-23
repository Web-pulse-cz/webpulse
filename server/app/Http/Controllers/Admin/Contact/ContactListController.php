<?php

namespace App\Http\Controllers\Admin\Contact;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Contact\ContactListResource;
use App\Models\Contact\Contact;
use App\Models\Contact\ContactList;
use Illuminate\Database\Eloquent\Builder;
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
                $query->where($searchString[0], 'like', '%'.$searchString[1].'%');
            } else {
                $query->where('name', 'like', '%'.$searchString.'%');
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
    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $contactList = ContactList::query()
                ->where('user_id', $request->user()->id)->find($id);
            if (! $contactList) {
                App::abort(404);
            }
        } else {
            $contactList = new ContactList;
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
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
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();

            return Response::json(['message' => 'An error occurred while updating contact list.'], 500);
        }

        return Response::json(ContactListResource::make($contactList));
    }

    /**
     * Hromadně přidá do seznamu kontakty přihlášeného uživatele odpovídající filtrům,
     * které v seznamu ještě nejsou. S `dry_run` pouze vrátí počet, který by se přidal.
     */
    public function addContacts(Request $request, int $id): JsonResponse
    {
        return $this->bulkUpdateContacts($request, $id, 'add');
    }

    /**
     * Hromadně odebere ze seznamu kontakty odpovídající filtrům.
     * S `dry_run` pouze vrátí počet, který by se odebral.
     */
    public function removeContacts(Request $request, int $id): JsonResponse
    {
        return $this->bulkUpdateContacts($request, $id, 'remove');
    }

    private function bulkUpdateContacts(Request $request, int $id, string $mode): JsonResponse
    {
        $contactList = ContactList::query()
            ->where('user_id', $request->user()->id)
            ->find($id);

        if (! $contactList) {
            App::abort(404);
        }

        $validator = Validator::make($request->all(), [
            'phone' => 'nullable|boolean',
            'email' => 'nullable|boolean',
            'goal' => 'nullable|boolean',
            'occupation' => 'nullable|boolean',
            'phases' => 'nullable|array',
            'phases.*' => 'integer',
            'sources' => 'nullable|array',
            'sources.*' => 'integer',
            'last_contacted' => 'nullable|in:1m,3m,6m,1y',
            'dry_run' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $query = Contact::query()->where('user_id', $request->user()->id);

        // Přidávají se jen kontakty mimo seznam, odebírají jen ty v seznamu
        $inList = function ($q) use ($contactList) {
            $q->where('contact_lists.id', $contactList->id);
        };
        if ($mode === 'add') {
            $query->whereDoesntHave('lists', $inList);
        } else {
            $query->whereHas('lists', $inList);
        }

        $this->applyContactFilters($query, $request);

        if ($request->boolean('dry_run')) {
            return Response::json(['count' => $query->count()]);
        }

        $contactIds = $query->pluck('id');

        if ($contactIds->isNotEmpty()) {
            if ($mode === 'add') {
                $contactList->contacts()->attach($contactIds->all());
            } else {
                $contactList->contacts()->detach($contactIds->all());
            }
        }

        return Response::json(['count' => $contactIds->count()]);
    }

    private function applyContactFilters(Builder $query, Request $request): void
    {
        // Ano/ne filtry na vyplněnost pole (null = nezáleží)
        foreach (['phone', 'email', 'goal', 'occupation'] as $field) {
            $value = $request->input($field);
            if ($value === null) {
                continue;
            }
            if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                $query->whereNotNull($field)->where($field, '!=', '');
            } else {
                $query->where(function ($q) use ($field) {
                    $q->whereNull($field)->orWhere($field, '=', '');
                });
            }
        }

        if (! empty($request->input('phases'))) {
            $query->whereIn('contact_phase_id', $request->input('phases'));
        }

        if (! empty($request->input('sources'))) {
            $query->whereIn('contact_source_id', $request->input('sources'));
        }

        // „Nekontaktován déle než X“ – kontakty bez data posledního kontaktu se počítají také
        if ($request->filled('last_contacted')) {
            $threshold = match ($request->input('last_contacted')) {
                '1m' => now()->subMonth(),
                '3m' => now()->subMonths(3),
                '6m' => now()->subMonths(6),
                '1y' => now()->subYear(),
            };
            $query->where(function ($q) use ($threshold) {
                $q->whereNull('last_contacted_at')->orWhere('last_contacted_at', '<', $threshold);
            });
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        if (! $id) {
            App::abort(400);
        }

        $contact_list = ContactList::query()
            ->where('user_id', $request->user()->id)
            ->find($id);

        if (! $contact_list) {
            App::abort(404);
        }

        return Response::json(ContactListResource::make($contact_list));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        if (! $id) {
            App::abort(400);
        }

        $contact_list = ContactList::query()
            ->where('user_id', $request->user()->id)
            ->find($id);

        if (! $contact_list) {
            App::abort(404);
        }

        $contact_list->delete();

        return Response::json();
    }
}
