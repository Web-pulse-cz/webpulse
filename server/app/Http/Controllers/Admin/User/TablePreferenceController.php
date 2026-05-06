<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\User\UserTablePreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TablePreferenceController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $slug = (string) $request->query('slug', '');
        if ($slug === '') {
            return Response::json(['error' => 'Missing slug.'], 422);
        }

        $siteId = $this->resolveSiteId($request);

        $preference = UserTablePreference::query()
            ->where('user_id', $request->user()->id)
            ->where('site_id', $siteId)
            ->where('table_slug', $slug)
            ->first();

        if (! $preference) {
            return Response::json(null);
        }

        return Response::json([
            'visible_columns' => $preference->visible_columns,
            'per_page' => $preference->per_page,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required|string|max:64',
            'visible_columns' => 'nullable|array',
            'visible_columns.*' => 'string|max:128',
            'per_page' => 'required|integer|min:1|max:1000',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $siteId = $this->resolveSiteId($request);

        $preference = UserTablePreference::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'site_id' => $siteId,
                'table_slug' => $request->input('slug'),
            ],
            [
                'visible_columns' => $request->input('visible_columns'),
                'per_page' => (int) $request->input('per_page'),
            ],
        );

        return Response::json([
            'visible_columns' => $preference->visible_columns,
            'per_page' => $preference->per_page,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $slug = (string) $request->query('slug', $request->input('slug', ''));
        if ($slug === '') {
            return Response::json(['error' => 'Missing slug.'], 422);
        }

        $siteId = $this->resolveSiteId($request);

        UserTablePreference::query()
            ->where('user_id', $request->user()->id)
            ->where('site_id', $siteId)
            ->where('table_slug', $slug)
            ->delete();

        return Response::json([]);
    }

    private function resolveSiteId(Request $request): ?int
    {
        $hash = $request->header('X-Site-Hash');
        if (! $hash) {
            return null;
        }

        return $this->handleSite($hash);
    }
}
