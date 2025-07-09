<?php

namespace App\Http\Controllers\Client\Event;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Event\EventCategoryResource;
use App\Models\Event\EventCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class EventCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);

        $query = EventCategory::query()
            ->orderBy('position', 'asc')
            ->orderBy('id', 'desc');

        $faqCategories = $query->get();

        return Response::json(EventCategoryResource::collection($faqCategories));
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        if (!$id) {
            return Response::json(['error' => 'Category ID is required'], 400);
        }

        $eventCategory = EventCategory::query()
            ->where('id', $id)
            ->first();

        if (!$eventCategory) {
            return Response::json(['error' => 'Category not found'], 404);
        }

        return Response::json(EventCategoryResource::make($eventCategory));
    }
}
