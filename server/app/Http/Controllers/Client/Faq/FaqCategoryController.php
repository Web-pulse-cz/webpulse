<?php

namespace App\Http\Controllers\Client\Faq;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Faq\FaqCategoryResource;
use App\Models\Faq\FaqCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class FaqCategoryController extends Controller
{
    public function index(Request $request, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = FaqCategory::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->where('active', true)
            ->with(['faqs'])
            ->orderBy('position', 'asc')
            ->orderBy('id', 'desc');

        $faqCategories = $query->get();

        return Response::json(FaqCategoryResource::collection($faqCategories));
    }

    public function show(Request $request, int $id, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if (!$id) {
            return Response::json(['error' => 'Category ID is required'], 400);
        }

        $faqCategory = FaqCategory::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->where('active', true)
            ->where('id', $id)
            ->with(['faqs'])
            ->first();

        if (!$faqCategory) {
            return Response::json(['error' => 'Category not found'], 404);
        }

        return Response::json(FaqCategoryResource::make($faqCategory));
    }
}
