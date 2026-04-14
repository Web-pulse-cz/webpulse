<?php

namespace App\Http\Controllers\Admin\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Customer\CustomerGroupResource;
use App\Models\Customer\CustomerGroup;
use App\Traits\Siteable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CustomerGroupController extends Controller
{
    use Siteable;

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));
        $query = CustomerGroup::withCount('customers')
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->get('search').'%');
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        } else {
            $query->orderBy('position');
        }

        if ($request->has('paginate')) {
            $groups = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => CustomerGroupResource::collection($groups->items()),
                'total' => $groups->total(),
                'perPage' => $groups->perPage(),
                'currentPage' => $groups->currentPage(),
                'lastPage' => $groups->lastPage(),
            ]);
        }

        return Response::json(CustomerGroupResource::collection($query->get()));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $group = CustomerGroup::find($id);
            if (! $group) {
                App::abort(404);
            }
        } else {
            $group = new CustomerGroup;
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_currency_id' => 'nullable|integer|exists:currencies,id',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();
            $group->fill($request->except(['sites']));
            $group->save();

            if ($request->has('sites')) {
                $this->saveSites($group, $request->get('sites', []));
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['message' => 'Chyba při ukládání skupiny.'], 500);
        }

        return Response::json(CustomerGroupResource::make($group->fresh('sites')));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));
        $group = CustomerGroup::withCount('customers')
            ->with('sites')
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);

        if (! $group) {
            App::abort(404);
        }

        return Response::json(CustomerGroupResource::make($group));
    }

    public function destroy(int $id): JsonResponse
    {
        $group = CustomerGroup::find($id);
        if (! $group) {
            App::abort(404);
        }

        $group->delete();

        return Response::json();
    }
}
