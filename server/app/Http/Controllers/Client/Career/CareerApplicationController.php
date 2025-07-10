<?php

namespace App\Http\Controllers\Client\Career;

use App\Events\CareerApplicationSaved;
use App\Http\Controllers\Controller;
use App\Models\Career\CareerApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CareerApplicationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);

        $validator = Validator::make($request->all(), [
            'career_id' => 'required|exists:careers,id',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'cover_letter' => 'nullable|string',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'salary_expectation' => 'nullable|numeric|min:0',
            'availability' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $careerApplication = new CareerApplication();
            $careerApplication->career_id = $request->input('career_id');
            $careerApplication->fill($request->all());
            $careerApplication->locale = $lang;
            $careerApplication->save();

            DB::commit();

            CareerApplicationSaved::dispatch($careerApplication);
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }

        return Response::json();
    }
}
