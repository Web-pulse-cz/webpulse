<?php

namespace App\Http\Controllers\Client\Faq;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Faq\FaqResource;
use App\Models\Faq\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class FaqController extends Controller
{
    public function index(Request $request, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);

        $query = Faq::query()
            ->where('active', true)
            ->with(['categories'])
            ->orderBy('position', 'asc')
            ->orderBy('id', 'desc');

        if ($request->has('categoryId') && $request->get('categoryId') !== null) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('faqs_in_categories.id', $request->get('categoryId'));
            });
        }

        $faqs = $query->get();

        return Response::json(FaqResource::collection($faqs));
    }
}
