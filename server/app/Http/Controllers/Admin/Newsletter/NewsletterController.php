<?php

namespace App\Http\Controllers\Admin\Newsletter;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Newsletter\NewsletterResource;
use App\Models\Newsletter\Newsletter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class NewsletterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Newsletter::query();

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where('firstname', '=', $searchString)
                    ->orWhere('lastname', 'like', '%' . $searchString . '%')
                    ->orWhere('addressing', 'like', '%' . $searchString . '%')
                    ->orWhere('email', 'like', '%' . $searchString . '%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $newsletters = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => NewsletterResource::collection($newsletters->items()),
                'total' => $newsletters->total(),
                'perPage' => $newsletters->perPage(),
                'currentPage' => $newsletters->currentPage(),
                'lastPage' => $newsletters->lastPage(),
            ]);
        }

        $newsletters = $query->get();
        return Response::json(NewsletterResource::collection($newsletters));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $newsletter = Newsletter::find($id);

        if (!$newsletter) {
            App::abort(404);
        }

        $newsletter->delete();
        return Response::json();
    }
}
