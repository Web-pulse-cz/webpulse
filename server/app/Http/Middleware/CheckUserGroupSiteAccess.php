<?php

namespace App\Http\Middleware;

use App\Models\Site\Site;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserGroupSiteAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $siteHash = $request->header('X-Site-Hash');

        if (! $siteHash) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user || ! $user->userGroup) {
            return $next($request);
        }

        $site = Site::query()->where('hash', $siteHash)->first();

        if (! $site) {
            return $next($request);
        }

        $groupSiteIds = $user->userGroup->sites()->pluck('site_id')->toArray();

        // If the group has no sites assigned, allow access (legacy groups)
        if (empty($groupSiteIds)) {
            return $next($request);
        }

        if (! in_array($site->id, $groupSiteIds)) {
            return response()->json([
                'message' => 'Vaše uživatelská skupina nemá přístup k této stránce.',
            ], 403);
        }

        return $next($request);
    }
}
