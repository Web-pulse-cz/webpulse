<?php

namespace App\Http\Controllers\Admin\Employee;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Employee\EmployeeDivisionResource;
use App\Models\Employee\EmployeeDivision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class EmployeeDivisionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $divisions = EmployeeDivision::withCount('employees')
            ->with('headEmployee')
            ->orderBy('position')
            ->get();

        return Response::json(EmployeeDivisionResource::collection($divisions));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $division = EmployeeDivision::find($id);
            if (! $division) {
                App::abort(404);
            }
        } else {
            $division = new EmployeeDivision;
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'head_employee_id' => 'nullable|integer|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();
            $division->fill($request->all());
            $division->save();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['message' => 'Chyba při ukládání divize.'], 500);
        }

        return Response::json(EmployeeDivisionResource::make($division->fresh('headEmployee')));
    }

    public function show(int $id): JsonResponse
    {
        $division = EmployeeDivision::withCount('employees')->with(['headEmployee', 'employees'])->find($id);
        if (! $division) {
            App::abort(404);
        }

        return Response::json(EmployeeDivisionResource::make($division));
    }

    public function destroy(int $id): JsonResponse
    {
        $division = EmployeeDivision::find($id);
        if (! $division) {
            App::abort(404);
        }

        $division->employees()->detach();
        $division->delete();

        return Response::json();
    }
}
