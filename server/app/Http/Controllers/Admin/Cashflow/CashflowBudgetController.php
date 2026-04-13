<?php

namespace App\Http\Controllers\Admin\Cashflow;

use App\Http\Controllers\Controller;
use App\Models\Cashflow\CashflowBudget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CashflowBudgetController extends Controller
{
    public function store(Request $request, ?int $id = null): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'categoryId' => 'nullable|integer|exists:cashflow_categories,id',
            'budget' => 'required|numeric',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|between:1900,2100',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $categoryId = $id;
        $month = $request->input('month');
        $year = $request->input('year');

        $cashflowBudget = CashflowBudget::query()
            ->where('cashflow_category_id', $categoryId)
            ->whereMonth('start_date', $month)
            ->whereYear('start_date', $year)
            ->whereMonth('end_date', $month)
            ->whereYear('end_date', $year)
            ->first();

        if (! $cashflowBudget) {
            $cashflowBudget = new CashflowBudget;
        }

        try {
            DB::beginTransaction();

            $cashflowBudget->fill([
                'amount' => $request->input('budget'),
                'user_id' => $request->user()->id,
                'type' => 'month',
                'start_date' => date('Y-m-d', strtotime("first day of $year-$month")),
                'end_date' => date('Y-m-d', strtotime("last day of $year-$month")),
            ]);
            $cashflowBudget->cashflow_category_id = $categoryId;
            $cashflowBudget->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();

            return Response::json(['errors' => $e->getMessage()], 500);
        }

        return Response::json();
    }
}
