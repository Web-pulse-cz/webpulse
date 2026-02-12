<?php

namespace App\Http\Controllers\Admin\Cashflow;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Cashflow\CashflowCategoryResource;
use App\Http\Resources\Admin\Cashflow\CashflowCategorySimpleResource;
use App\Models\Cashflow\Cashflow;
use App\Models\Cashflow\CashflowBudget;
use App\Models\Cashflow\CashflowCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CashflowCategoryController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $month = $request->has('month') ? (int)$request->month : (int)date('m');
        $year = $request->has('year') ? (int)$request->year : (int)date('Y');

        // determine if we want to load only categories without cashflows and budgets (used on admin homepage)
        $onlyCategories = $request->has('only_categories') && (bool)$request->only_categories;
        $onlyCategories = (bool)$onlyCategories;

        $categoriesQuery = CashflowCategory::query();

        if (!$onlyCategories) {
            $categoriesQuery->with([
                'budgets' => function ($query) use ($month, $year) {
                    $query->whereMonth('start_date', $month)
                        ->whereYear('start_date', $year)
                        ->whereMonth('end_date', $month)
                        ->whereYear('end_date', $year);
                },
                'cashflows' => function ($query) use ($request, $month, $year) {
                    $query->whereMonth('date', $month)
                        ->whereYear('date', $year);
                    if ($request->has('dayFrom')) {
                        $query->whereDay('date', '>=', $request->dayFrom);
                    }
                    if ($request->has('dayTo')) {
                        $query->whereDay('date', '<=', $request->dayTo);
                    }
                }
            ]);
        } else {
            $categoriesQuery->without(['budgets', 'cashflows']);
        }

        $categoriesQuery->where('user_id', $request->user()->id);

        if (!$onlyCategories) {
            $incomeQuery = Cashflow::query()
                ->where('user_id', $request->user()->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->where('type', 'income');
        }

        $categories = $categoriesQuery->get();

        if (!$onlyCategories) {
            foreach ($categories as $category) {
                if ($category->budgets->isEmpty()) {
                    $budget = new CashflowBudget();
                    $budget->fill([
                        'user_id' => $request->user()->id,
                        'type' => 'month',
                        'amount' => 1000,
                        'start_date' => date('Y-m-01', strtotime("first day of $year-$month")),
                        'end_date' => date('Y-m-t', strtotime("last day of $year-$month")),
                        'cashflow_category_id' => $category->id
                    ]);
                    $budget->cashflow_category_id = $category->id;
                    $budget->save();

                    $category->budgets->push($budget);
                }
            }
        }

        if ($onlyCategories) {
            return Response::json(CashflowCategorySimpleResource::collection($categories));
        }

        return Response::json([
            'categories' => CashflowCategoryResource::collection($categories),
            'income' => \App\Http\Resources\Admin\Cashflow\CashflowResource::collection($incomeQuery->get())
        ]);
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $cashflowCategory = CashflowCategory::find($id);
            $cashflowCategory->update($request->all());
        } else {
            $cashflowCategory = new CashflowCategory();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $cashflowCategory->fill($request->all());
            $cashflowCategory->user_id = $request->user()->id;

            $cashflowCategory->save();

            if (!$id) {
                $cashflowCategory->budgets()->create([
                    'start_date' => date('Y-m-01'),
                    'end_date' => date('Y-m-t'),
                    'amount' => 1000
                ]);
            }

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['errors' => $e->getMessage()], 422);
        }

        return Response::json();
    }

    public function show(Request $request, int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $cashflowCategory = CashflowCategory::with([
            'budgets' => function ($query) {
                $query->whereMonth('start_date', date('m'))
                    ->whereYear('start_date', date('Y'))
                    ->whereMonth('end_date', date('m'))
                    ->whereYear('end_date', date('Y'));
            },
            'cashflows'
        ])
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();
        if (!$cashflowCategory) {
            App::abort(404);
        }

        return Response::json(CashflowCategoryResource::make($cashflowCategory));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $cashflowCategory = CashflowCategory::where('user_id', $request->user()->id)->find($id);
        if (!$cashflowCategory) {
            App::abort(404);
        }

        $cashflowCategory->delete();
        return Response::json();
    }
}
