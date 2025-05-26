<?php

namespace App\Http\Controllers;

use App\Services\FileManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;

class FilemanagerController extends Controller
{
    protected FileManagerService $fileManagerService;

    public function __construct(FileManagerService $fileManagerService)
    {
        $this->fileManagerService = $fileManagerService;
    }

    public function getImageFormats(Request $request): JsonResponse
    {
        if (!$request->has('securityKey') || $request->get('securityKey') !== 'your_security_key_here') {
            return Response::json(['error' => 'Unauthorized'], 401);
        }
        return Response::json($this->fileManagerService->getImageFormats($request->get('type', null), $request->get('format', null)));
    }

    public function uploadImages(Request $request): JsonResponse
    {
        if (!$request->has('securityKey') || $request->get('securityKey') !== 'your_security_key_here') {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|string', // e.g., 'product', 'blog', etc.
            'format' => 'nullable|string', // e.g., 'thumbnail', 'full', etc.
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        try {
            $result = $this->fileManagerService->uploadImages(
                $request->file('images'),
                $request->get('type'),
                $request->get('format', null),
                $request->get('keepName', 0)
            );
        } catch (\Throwable|\Exception $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
        return Response::json($result);
    }

    /*public function uploadFiles(Request $request): JsonResponse
    {
    if(!$request->has('securityKey') || $request->get('securityKey') !== 'your_security_key_here') {
            return Response::json(['error' => 'Unauthorized'], 401);
        }
        $result = $this->fileManagerService->uploadFiles($request);
        return Response::json($result);
    }*/
}
