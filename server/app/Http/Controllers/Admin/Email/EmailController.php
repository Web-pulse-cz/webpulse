<?php

namespace App\Http\Controllers\Admin\Email;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Email\EmailResource;
use App\Models\Email\Email;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class EmailController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Email::query();

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where('from', 'like', '%' . $searchString . '%')
                    ->orWhere('to', 'like', '%' . $searchString . '%')
                    ->orWhere('cc', 'like', '%' . $searchString . '%')
                    ->orWhere('bcc', 'like', '%' . $searchString . '%')
                    ->orWhere('status', 'like', '%' . $searchString . '%')
                    ->orWhere('template', 'like', '%' . $searchString . '%')
                    ->orWhere('locale', 'like', '%' . $searchString . '%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $emails = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => EmailResource::collection($emails->items()),
                'total' => $emails->total(),
                'perPage' => $emails->perPage(),
                'currentPage' => $emails->currentPage(),
                'lastPage' => $emails->lastPage(),
            ]);
        }

        $emails = $query->get();
        return Response::json(EmailResource::collection($emails));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $email = Email::find($id);
        if (!$email) {
            App::abort(404);
        }

        return Response::json(EmailResource::make($email));
    }
}
