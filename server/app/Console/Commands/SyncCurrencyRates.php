<?php

namespace App\Console\Commands;

use App\Models\Currency\Currency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncCurrencyRates extends Command
{
    protected $signature = 'currency:sync-rates';

    protected $description = 'Command description';

    public function handle(): void
    {
        $this->output->title('Syncing currency rates');

        $currencies = Currency::query()->pluck(
            'id', 'code'
        )->toArray();

        $date = date('Y-m-d');

        $response = Http::get('https://api.cnb.cz/cnbapi/exrates/daily', [
            'date' => $date,
            'lang' => 'CZ',
        ]);

        $response = $response->json();

        $this->output->progressStart(count($currencies));

        foreach ($response['rates'] as $item) {
            if (array_key_exists($item['currencyCode'], $currencies)) {
                $currency = Currency::query()->where('code', '=', $item['currencyCode'])->first();
                if ($currency) {
                    $currency->fill([
                        'rate' => $item['rate'],
                    ]);
                    $currency->save();
                }
            }
            $this->output->progressAdvance();
        }
    }
}
