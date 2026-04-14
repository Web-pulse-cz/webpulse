<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Reusable file upload/download/delete for any controller
 * whose model uses the Fileable trait.
 *
 * Usage in controller:
 *   use HasFiles;
 *   public function uploadFile(Request $request, int $id) {
 *       return $this->handleUploadFile($request, Employee::class, $id, 'files/employees', EmployeeResource::class, ['divisions', 'sites']);
 *   }
 */
trait HasFiles
{
    protected function handleUploadFile(
        Request $request,
        string $modelClass,
        int $id,
        string $directory,
        ?string $resourceClass = null,
        array $eagerLoad = [],
    ): JsonResponse {
        $model = $modelClass::find($id);
        if (! $model) {
            App::abort(404);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:20480',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        $file = $request->file('file');
        $model->attachUploadedFile($file, $directory . '/' . $model->id);

        if ($resourceClass) {
            return Response::json($resourceClass::make(
                $eagerLoad ? $model->fresh($eagerLoad) : $model->fresh()
            ));
        }

        return Response::json(['files' => $model->fresh()->files()]);
    }

    protected function handleDownloadFile(string $modelClass, int $modelId, int $fileId)
    {
        $model = $modelClass::find($modelId);
        if (! $model) {
            App::abort(404);
        }

        $file = DB::table('fileables')
            ->where('id', $fileId)
            ->where('fileable_id', $modelId)
            ->where('fileable_type', get_class($model))
            ->first();

        if (! $file) {
            App::abort(404);
        }

        $disk = $file->disk ?? 'public';
        if (! Storage::disk($disk)->exists($file->path)) {
            App::abort(404);
        }

        return response()->download(Storage::disk($disk)->path($file->path), $file->name, [
            'Content-Type' => $file->mime_type,
        ]);
    }

    protected function handleDeleteFile(string $modelClass, int $modelId, int $fileId): JsonResponse
    {
        $model = $modelClass::find($modelId);
        if (! $model) {
            App::abort(404);
        }

        $model->removeFile($fileId);

        return Response::json();
    }
}
