<?php

namespace App\Http\Controllers\Admin\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Client\ClientResource;
use App\Http\Resources\Admin\Client\ClientSimpleResource;
use App\Jobs\Fakturoid\SyncClientToFakturoidJob;
use App\Models\Client\Client;
use App\Traits\Siteable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    use Siteable;

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Client::query()
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('ico', 'like', '%'.$search.'%')
                    ->orWhere('city', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        } else {
            $query->orderBy('name', 'asc');
        }

        if ($request->has('paginate')) {
            $clients = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => ClientSimpleResource::collection($clients->items()),
                'total' => $clients->total(),
                'perPage' => $clients->perPage(),
                'currentPage' => $clients->currentPage(),
                'lastPage' => $clients->lastPage(),
            ]);
        }

        return Response::json(ClientSimpleResource::collection($query->get()));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $client = Client::find($id);
            if (! $client) {
                App::abort(404);
            }
        } else {
            $client = new Client;
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'country_id' => 'nullable|integer|exists:countries,id',
            'delivery_country_id' => 'nullable|integer|exists:countries,id',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $client->fill($request->all());
            $client->local_updated_at = now();
            $client->save();

            if ($request->has('sites')) {
                $this->saveSites($client, $request->get('sites', []));
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['message' => 'Chyba při ukládání klienta.'], 500);
        }

        // Push to Fakturoid
        $siteId = $this->handleSite($request->header('X-Site-Hash'));
        SyncClientToFakturoidJob::dispatch($client->id, $siteId);

        return Response::json(ClientResource::make($client));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $client = Client::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);
        if (! $client) {
            App::abort(404);
        }

        return Response::json(ClientResource::make($client));
    }

    public function destroy(int $id): JsonResponse
    {
        $client = Client::find($id);
        if (! $client) {
            App::abort(404);
        }

        $client->delete();

        return Response::json();
    }
}
