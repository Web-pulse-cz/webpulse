<?php

namespace App\Http\Controllers\Client\Page;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Page\PageResource;
use App\Models\Page\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PageController extends Controller
{
    public function show(Request $request, int $id, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);

        if (!$id) {
            return Response::json(['error' => 'Page ID is required'], 400);
        }

        $page = Page::query()
            ->where('active', true)
            ->find($id);

        if (!$page) {
            return Response::json(['error' => 'Page not found'], 404);
        }

        return Response::json(PageResource::make($page));
    }
}
