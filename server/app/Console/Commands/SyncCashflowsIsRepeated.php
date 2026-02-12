<?php

namespace App\Console\Commands;

use App\Models\Cashflow\Cashflow;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SyncCashflowsIsRepeated extends Command
{
    protected $signature = 'cashflows:sync-is-repeated';

    protected $description = 'Add cashflows with attr is_repeated to current month.';

    public function handle()
    {
        $this->output->title('Syncing cashflows with attr is_repeated');

        $current = Carbon::now();

        $month = $current->subMonth()->month;
        $year = $current->subMonth()->year;

        $cashflows = Cashflow::query()
            ->where(function ($query) {
                $query->where('is_repeated', 1)
                    ->orWhere('is_repeated', true);
            })->whereRaw('month(date) = ' . $month . '  AND year(date) = ' . $year)
            ->get();

        $this->output->progressStart($cashflows->count());
        foreach ($cashflows as $cashflow) {
            $newCashflow = new Cashflow();

            $newCashflow->fill([
                'cashflow_category_id' => $cashflow->cashflow_category_id,
                'user_id' => $cashflow->user_id,
                'amount' => $cashflow->amount,
                'type' => $cashflow->type,
                'description' => $cashflow->description,
                'is_repeated' => $cashflow->is_repeated,
            ]);
            $newCashflow->date = Carbon::parse($cashflow->date)->addMonth()->format('Y-m-d');
            $newCashflow->save();

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
    }
}
