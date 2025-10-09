<?php

namespace App\Http\Controllers\Admin\Cashflow;

use App\Http\Controllers\Controller;
use App\Models\Cashflow\Cashflow;
use App\Models\Currency\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CashflowController extends Controller
{
    public function store(Request $request, int $id = null): JsonResponse
    {
        $categoryId = $request->input('categoryId');
        $currencyId = $request->has('currencyId') ? $request->input('currencyId') : 1;
        $formattedDate = $request->input('formattedDate');
        $records = $request->input('records');
        $type = $request->input('type');

        $validator = Validator::make($request->all(), [
            'categoryId' => 'nullable|integer|exists:cashflow_categories,id',
            'currencyId' => 'nullable|integer|exists:currencies,id',
            'formattedDate' => 'required|date_format:Y-m-d\TH:i:s.v\Z',
            'type' => 'required|in:expense,income',
            'records' => 'required|array',
            'records.*.description' => 'nullable|string',
            'records.*.amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        foreach ($records as $record) {
            if ($record['amount'] == 0) {
                Cashflow::query()
                    ->where('id', (int)$record['id'])
                    ->delete();
            }

            DB::beginTransaction();
            try {
                if ($record['id']) {
                    $cashflow = Cashflow::query()
                        ->where('user_id', $request->user()->id)
                        ->find($record['id']);
                    if (!$cashflow) {
                        throw new \Exception('Cashflow not found');
                    }
                } else {
                    $cashflow = new Cashflow();
                }

                $amount = $record['amount'];
                if ($currencyId != 1) {
                    $currency = Currency::find($currencyId);
                    if (!$currency) {
                        throw new \Exception('Currency not found');
                    }
                    $amount = $currency->convertToBase($amount);
                }

                $cashflow->fill([
                    'amount' => $amount,
                    'description' => $record['description'],
                    'date' => new \DateTime($formattedDate),
                ]);
                $cashflow->type = $type;
                $cashflow->user_id = $request->user()->id;
                if ($categoryId && $type != 'income') {
                    $cashflow->cashflow_category_id = $categoryId;
                }
                $cashflow->save();

                DB::commit();
            } catch (\Throwable|\Exception $e) {
                DB::rollBack();
                continue;
            }
        }

        return Response::json();
    }
}
