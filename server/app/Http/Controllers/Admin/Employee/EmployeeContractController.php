<?php

namespace App\Http\Controllers\Admin\Employee;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Employee\EmployeeContractResource;
use App\Models\Employee\EmployeeContract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class EmployeeContractController extends Controller
{
    public function index(Request $request, int $employeeId): JsonResponse
    {
        $contracts = EmployeeContract::where('employee_id', $employeeId)
            ->orderBy('date_from', 'desc')
            ->get();

        return Response::json(EmployeeContractResource::collection($contracts));
    }

    public function store(Request $request, int $employeeId, ?int $id = null): JsonResponse
    {
        if ($id) {
            $contract = EmployeeContract::where('employee_id', $employeeId)->find($id);
            if (! $contract) {
                App::abort(404);
            }
        } else {
            $contract = new EmployeeContract;
            $contract->employee_id = $employeeId;
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'currency_id' => 'nullable|integer|exists:currencies,id',
            'file' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $contract->fill($request->except(['file']));

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('contracts/'.$employeeId, 'public');
                $contract->file_path = $path;
            }

            $contract->save();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['message' => 'Chyba při ukládání smlouvy.'], 500);
        }

        return Response::json(EmployeeContractResource::make($contract));
    }

    public function show(int $employeeId, int $id): JsonResponse
    {
        $contract = EmployeeContract::where('employee_id', $employeeId)->find($id);
        if (! $contract) {
            App::abort(404);
        }

        return Response::json(EmployeeContractResource::make($contract));
    }

    public function destroy(int $employeeId, int $id): JsonResponse
    {
        $contract = EmployeeContract::where('employee_id', $employeeId)->find($id);
        if (! $contract) {
            App::abort(404);
        }

        $contract->delete();

        return Response::json();
    }

    public function pdf(int $employeeId, int $id)
    {
        $contract = EmployeeContract::with('employee')->where('employee_id', $employeeId)->find($id);
        if (! $contract) {
            App::abort(404);
        }

        $pdf = Pdf::loadView('pdf.employee-contract', [
            'contract' => $contract,
            'employee' => $contract->employee,
        ]);

        return $pdf->download('smlouva-'.$contract->employee->last_name.'-'.$contract->id.'.pdf');
    }
}
