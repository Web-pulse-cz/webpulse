<?php

namespace App\Http\Controllers\Client\Newsletter;

use App\Http\Controllers\Controller;
use App\Models\Newsletter\Newsletter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    public function store(Request $request, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        /*$validator = $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:newsletters,email',
        ]);

        if ($validator->fails()) {
            return Response::json([
                'message' => $validator->errors()->first(),
            ], 422);
        }*/

        DB::beginTransaction();
        try {
            $newsletter = new Newsletter();

            $newsletter->fill($request->all());
            $newsletter->locale = $lang ?? app()->getLocale();

            $newsletter->saveSites($newsletter, [$siteId]);

            $newsletter->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while saving newsletter.'], 500);
        }

        return Response::json();
    }
}
