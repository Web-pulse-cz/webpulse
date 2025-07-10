<?php

namespace App\Http\Controllers\Admin\Career;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Career\CareerApplicationResource;
use App\Models\Career\CareerApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CareerApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = CareerApplication::query()
            ->with('career');

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where('firstname', '=', $searchString)
                    ->orWhere('lastname', '=', $searchString)
                    ->orWhere('email', '=', $searchString)
                    ->orWhere('phone', '=', $searchString)
                    ->orWhereHas('career', function ($q) use ($searchString) {
                        $q->where('name', 'like', '%' . $searchString . '%')
                            ->orWhere('code', 'like', '%' . $searchString . '%');
                    });
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $careerApplications = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => CareerApplicationResource::collection($careerApplications->items()),
                'total' => $careerApplications->total(),
                'perPage' => $careerApplications->perPage(),
                'currentPage' => $careerApplications->currentPage(),
                'lastPage' => $careerApplications->lastPage(),
            ]);

        }
        $careerApplications = $query->get();
        return Response::json(CareerApplicationResource::collection($careerApplications));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, int $id = null): JsonResponse
    {
        if($id) {
            $careerApplication = CareerApplication::find($id);
            if(!$careerApplication) {
                App::abort(404);
            }
        } else {
            $careerApplication = new CareerApplication();
        }

        $validator = Validator::make($request->all(), [
            'career_id' => 'required|exists:careers,id',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        if($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $careerApplication->fill($request->all());
            $careerApplication->user_id = $request->user()->id;
            $careerApplication->save();

            DB::commit();
        } catch (\Throwable | \Exception $e) {
            DB::rollBack();
            return Response::json(['error' => 'An error occurred while processing your request.'], 500);
        }

        return Response::json();
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        if(!$id) {
            App::abort(400);
        }

        $careerApplication = CareerApplication::with(['career'])->find($id);
        if(!$careerApplication) {
            App::abort(404);
        }

        return Response::json(CareerApplicationResource::make($careerApplication));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        if(!$id) {
            App::abort(400);
        }

        $careerApplication = CareerApplication::find($id);
        if(!$careerApplication) {
            App::abort(404);
        }

        $careerApplication->delete();
        return Response::json();
    }
}
