<?php

namespace App\Console\Commands\Manual;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Spatie\SimpleExcel\SimpleExcelWriter;

class AresCommand extends Command
{
    protected $signature = 'ares:search';

    protected $description = 'Command description';

    public function handle(): void
    {
        $this->output->title('Searching for companies...');
        $from = 0;
        $count = 10;
        $finalCount = 1000;
        $data = [['IČO', 'Obchodní název', 'Právní forma', 'Sídlo']];
        $client = new Client;

        $this->output->progressStart($finalCount);
        do {
            $request = [
                'pocet' => $count,
                'razeni' => [],
                'sidlo' => [
                    'kodMestskeCastiObvodu' => 500208,
                    'kodObce' => 554782,
                    'kodUlice' => 480681,
                ],
                'start' => $from,
            ];
            $res = $client->request('POST', 'https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/vyhledat', [
                'json' => $request,
            ]);
            $body = json_decode($res->getBody()->getContents(), true);

            if (! isset($body['ekonomickeSubjekty'])) {
                break;
            }

            foreach ($body['ekonomickeSubjekty'] as $item) {
                try {
                    $data[] = [
                        array_key_exists('ico', $item) ? (int) $item['ico'] : 'bez IČO',
                        $item['obchodniJmeno'],
                        (int) $item['pravniForma'],
                        $item['sidlo']['textovaAdresa'],
                    ];
                } catch (\Throwable $th) {
                    dd($th->getMessage());

                    continue;
                }
                $this->output->progressAdvance();
            }

            $from += $count;
        } while ($from < $finalCount);

        $this->output->progressFinish();

        $writer = SimpleExcelWriter::create(storage_path('app/companies.xlsx'))->noHeaderRow();
        foreach ($data as $row) {
            $writer->addRow($row);
        }
    }
}
