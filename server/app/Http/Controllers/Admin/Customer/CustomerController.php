<?php

namespace App\Http\Controllers\Admin\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Customer\CustomerResource;
use App\Http\Resources\Admin\Customer\CustomerSimpleResource;
use App\Models\Customer\Customer;
use App\Traits\Siteable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
	use Siteable;

	public function index(Request $request): JsonResponse
	{
		$siteId = $this->handleSite($request->header('X-Site-Hash'));
		$query = Customer::with('group')->whereRelation('sites', 'site_id', $siteId);

		if ($request->filled('search')) {
			$search = $request->get('search');
			$query->where(function ($q) use ($search) {
				$q->where('first_name', 'like', '%' . $search . '%')
					->orWhere('last_name', 'like', '%' . $search . '%')
					->orWhere('email', 'like', '%' . $search . '%')
					->orWhere('phone', 'like', '%' . $search . '%')
					->orWhere('company_name', 'like', '%' . $search . '%');
			});
		}

		if ($request->filled('status')) {
			$query->where('status', $request->get('status'));
		}

		if ($request->filled('customer_group_id')) {
			$query->where('customer_group_id', $request->get('customer_group_id'));
		}

		if ($request->has('orderWay') && $request->get('orderBy')) {
			$query->orderBy($request->get('orderBy'), $request->get('orderWay'));
		} else {
			$query->orderBy('last_name', 'asc');
		}

		if ($request->has('paginate')) {
			$customers = $query->paginate($request->get('paginate'));

			return Response::json([
				'data' => CustomerSimpleResource::collection($customers->items()),
				'total' => $customers->total(),
				'perPage' => $customers->perPage(),
				'currentPage' => $customers->currentPage(),
				'lastPage' => $customers->lastPage(),
			]);
		}

		return Response::json(CustomerSimpleResource::collection($query->get()));
	}

	public function store(Request $request, int $id = null): JsonResponse
	{
		if ($id) {
			$customer = Customer::find($id);
			if (!$customer) {
				App::abort(404);
			}
		} else {
			$customer = new Customer();
		}

		$validator = Validator::make($request->all(), [
			'first_name' => 'required|string|max:255',
			'last_name' => 'required|string|max:255',
			'email' => 'nullable|email|max:255',
			'rating' => 'nullable|integer|min:1|max:5',
			'customer_group_id' => 'nullable|integer|exists:customer_groups,id',
			'country_id' => 'nullable|integer|exists:countries,id',
			'currency_id' => 'nullable|integer|exists:currencies,id',
		]);

		if ($validator->fails()) {
			return Response::json($validator->errors(), 400);
		}

		try {
			DB::beginTransaction();
			$customer->fill($request->except(['sites']));
			$customer->save();

			if ($request->has('sites')) {
				$this->saveSites($customer, $request->get('sites', []));
			}

			DB::commit();
		} catch (\Throwable $e) {
			DB::rollBack();
			return Response::json(['message' => 'Chyba při ukládání zákazníka.'], 500);
		}

		return Response::json(CustomerResource::make(
			$customer->fresh(['group', 'vouchers', 'sites'])
		));
	}

	public function show(Request $request, int $id): JsonResponse
	{
		$siteId = $this->handleSite($request->header('X-Site-Hash'));
		$customer = Customer::with(['group', 'vouchers', 'sites'])
			->whereRelation('sites', 'site_id', $siteId)
			->find($id);

		if (!$customer) {
			App::abort(404);
		}

		return Response::json(CustomerResource::make($customer));
	}

	public function destroy(int $id): JsonResponse
	{
		$customer = Customer::find($id);
		if (!$customer) {
			App::abort(404);
		}

		$customer->delete();
		return Response::json();
	}
}
