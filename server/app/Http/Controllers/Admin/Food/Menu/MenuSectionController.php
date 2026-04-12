<?php

namespace App\Http\Controllers\Admin\Food\Menu;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Food\Menu\MenuSectionResource;
use App\Models\Food\Menu\MenuSection;
use App\Traits\Siteable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class MenuSectionController extends Controller
{
	use Siteable;

	public function index(Request $request): JsonResponse
	{
		$siteId = $this->handleSite($request->header('X-Site-Hash'));
		$query = MenuSection::whereRelation('sites', 'site_id', $siteId);

		if ($request->filled('search')) {
			$query->where('name', 'like', '%' . $request->get('search') . '%');
		}

		if ($request->has('orderWay') && $request->get('orderBy')) {
			$query->orderBy($request->get('orderBy'), $request->get('orderWay'));
		} else {
			$query->orderBy('position');
		}

		if ($request->has('paginate')) {
			$sections = $query->paginate($request->get('paginate'));

			return Response::json([
				'data' => MenuSectionResource::collection($sections->items()),
				'total' => $sections->total(),
				'perPage' => $sections->perPage(),
				'currentPage' => $sections->currentPage(),
				'lastPage' => $sections->lastPage(),
			]);
		}

		return Response::json(MenuSectionResource::collection($query->get()));
	}

	public function store(Request $request, int $id = null): JsonResponse
	{
		if ($id) {
			$section = MenuSection::find($id);
			if (!$section) {
				App::abort(404);
			}
		} else {
			$section = new MenuSection();
		}

		$validator = Validator::make($request->all(), [
			'name' => 'required|string|max:255',
		]);

		if ($validator->fails()) {
			return Response::json($validator->errors(), 400);
		}

		try {
			DB::beginTransaction();
			$section->fill($request->all());
			$section->save();
			$section->saveSites($section, $request->get('sites', []));
			DB::commit();
		} catch (\Throwable $e) {
			DB::rollBack();
			return Response::json(['message' => 'Chyba při ukládání sekce.'], 500);
		}

		return Response::json(MenuSectionResource::make($section));
	}

	public function show(int $id): JsonResponse
	{
		$section = MenuSection::with('sites')->find($id);
		if (!$section) {
			App::abort(404);
		}

		return Response::json(MenuSectionResource::make($section));
	}

	public function destroy(int $id): JsonResponse
	{
		$section = MenuSection::find($id);
		if (!$section) {
			App::abort(404);
		}

		$section->delete();
		return Response::json();
	}
}
