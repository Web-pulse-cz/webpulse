<?php

namespace App\Http\Controllers\Client\Setting;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Setting\SettingResource;
use App\Models\Setting\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SettingController extends Controller
{
    public function index(Request $request, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);

        $settings = Setting::query()
            ->get();

        return Response::json(SettingResource::collection($settings));
    }
}
