<?php

namespace App\Http\Controllers\Client\Newsletter;

use App\Http\Controllers\Controller;
use App\Models\Newsletter\Newsletter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    public function store(Request $request, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);

        $validator = $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:newsletters,email',
        ]);

        if ($validator->fails()) {
            return Response::json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $newsletter = new Newsletter();

        $newsletter->fill($request->all());
        $newsletter->locale = $lang ?? app()->getLocale();

        $newsletter->save();

        return Response::json();
    }
}
