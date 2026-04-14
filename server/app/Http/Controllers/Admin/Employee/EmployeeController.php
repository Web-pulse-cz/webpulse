<?php

namespace App\Http\Controllers\Admin\Employee;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Employee\EmployeeResource;
use App\Http\Resources\Admin\Employee\EmployeeSimpleResource;
use App\Models\Employee\Employee;
use App\Traits\HasFiles;
use App\Traits\Siteable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    use Siteable, HasFiles;

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Employee::query()
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%'.$search.'%')
                    ->orWhere('last_name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('employee_number', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('division_id')) {
            $query->whereHas('divisions', fn ($q) => $q->where('employee_divisions.id', $request->get('division_id')));
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        } else {
            $query->orderBy('last_name', 'asc');
        }

        if ($request->has('paginate')) {
            $employees = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => EmployeeSimpleResource::collection($employees->items()),
                'total' => $employees->total(),
                'perPage' => $employees->perPage(),
                'currentPage' => $employees->currentPage(),
                'lastPage' => $employees->lastPage(),
            ]);
        }

        return Response::json(EmployeeSimpleResource::collection($query->get()));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $employee = Employee::find($id);
            if (! $employee) {
                App::abort(404);
            }
        } else {
            $employee = new Employee;
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'country_id' => 'nullable|integer|exists:countries,id',
            'currency_id' => 'nullable|integer|exists:currencies,id',
            'date_of_birth' => 'nullable|date',
            'date_hired' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $employee->fill($request->except(['divisions', 'sites']));
            $employee->save();

            if ($request->has('divisions')) {
                $employee->divisions()->sync($request->get('divisions', []));
            }

            if ($request->has('sites')) {
                $this->saveSites($employee, $request->get('sites', []));
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['message' => 'Chyba při ukládání zaměstnance.'], 500);
        }

        return Response::json(EmployeeResource::make(
            $employee->fresh(['divisions', 'contracts', 'activeContract', 'sites'])
        ));
    }

    public function show(int $id): JsonResponse
    {
        $employee = Employee::with(['divisions', 'contracts', 'activeContract', 'sites'])->find($id);
        if (! $employee) {
            App::abort(404);
        }

        return Response::json(EmployeeResource::make($employee));
    }

    public function destroy(int $id): JsonResponse
    {
        $employee = Employee::find($id);
        if (! $employee) {
            App::abort(404);
        }

        $employee->removeAllFiles();
        $employee->delete();

        return Response::json();
    }

    public function uploadFile(Request $request, int $id): JsonResponse
    {
        return $this->handleUploadFile($request, Employee::class, $id, 'files/employees', EmployeeResource::class, ['divisions', 'contracts', 'activeContract', 'sites']);
    }

    public function downloadFile(int $employeeId, int $fileId)
    {
        return $this->handleDownloadFile(Employee::class, $employeeId, $fileId);
    }

    public function deleteFile(int $employeeId, int $fileId): JsonResponse
    {
        return $this->handleDeleteFile(Employee::class, $employeeId, $fileId);
    }
}
