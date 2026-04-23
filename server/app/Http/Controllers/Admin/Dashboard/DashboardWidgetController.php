<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User\UserDashboardWidget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class DashboardWidgetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $widgets = UserDashboardWidget::query()
            ->where('user_id', $request->user()->id)
            ->where('site_id', $siteId)
            ->orderBy('position')
            ->get(['widget_key', 'position', 'size', 'enabled']);

        return Response::json(['data' => $widgets]);
    }

    public function store(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $validated = $request->validate([
            'widgets' => 'required|array',
            'widgets.*.widget_key' => 'required|string|max:64',
            'widgets.*.position' => 'required|integer|min:0',
            'widgets.*.size' => 'required|string|in:half,full',
            'widgets.*.enabled' => 'required|boolean',
        ]);

        DB::transaction(function () use ($validated, $request, $siteId) {
            UserDashboardWidget::query()
                ->where('user_id', $request->user()->id)
                ->where('site_id', $siteId)
                ->delete();

            foreach ($validated['widgets'] as $widget) {
                UserDashboardWidget::create([
                    'user_id' => $request->user()->id,
                    'site_id' => $siteId,
                    'widget_key' => $widget['widget_key'],
                    'position' => $widget['position'],
                    'size' => $widget['size'],
                    'enabled' => $widget['enabled'],
                ]);
            }
        });

        $widgets = UserDashboardWidget::query()
            ->where('user_id', $request->user()->id)
            ->where('site_id', $siteId)
            ->orderBy('position')
            ->get(['widget_key', 'position', 'size', 'enabled']);

        return Response::json(['data' => $widgets]);
    }
}
