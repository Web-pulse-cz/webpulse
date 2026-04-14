<?php

namespace App\Http\Controllers\Admin\Shift;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Shift\ShiftTemplateResource;
use App\Models\Shift\ShiftTemplate;
use App\Traits\Siteable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ShiftTemplateController extends Controller
{
    use Siteable;

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        return Response::json(ShiftTemplateResource::collection(
            ShiftTemplate::query()
                ->whereRelation('sites', 'site_id', $siteId)
                ->orderBy('name')
                ->get()
        ));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $template = ShiftTemplate::find($id);
            if (! $template) {
                App::abort(404);
            }
        } else {
            $template = new ShiftTemplate;
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();
            $template->fill($request->all());
            $template->save();

            // Auto-assign site from header if no sites provided
            $siteId = $this->handleSite($request->header('X-Site-Hash'));
            $sites = $request->get('sites', [$siteId]);
            $this->saveSites($template, $sites);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['message' => 'Chyba při ukládání šablony směny.'], 500);
        }

        return Response::json(ShiftTemplateResource::make($template));
    }

    public function show(int $id): JsonResponse
    {
        $template = ShiftTemplate::with('sites')->find($id);
        if (! $template) {
            App::abort(404);
        }

        return Response::json(ShiftTemplateResource::make($template->fresh('sites')));
    }

    public function destroy(int $id): JsonResponse
    {
        $template = ShiftTemplate::find($id);
        if (! $template) {
            App::abort(404);
        }

        $template->delete();

        return Response::json();
    }
}
