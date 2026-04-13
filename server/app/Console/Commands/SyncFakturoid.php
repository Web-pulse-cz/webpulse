<?php

namespace App\Console\Commands;

use App\Models\Site\Site;
use App\Services\FakturoidService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncFakturoid extends Command
{
    protected $signature = 'fakturoid:sync {--full : Run full sync instead of incremental} {--site= : Sync only specific site ID}';

    protected $description = 'Sync clients and invoices with Fakturoid per site';

    public function handle(): int
    {
        $since = $this->option('full') ? null : now()->subMinutes(20);

        $query = Site::whereNotNull('fakturoid_client_id')
            ->whereNotNull('fakturoid_client_secret');

        if ($this->option('site')) {
            $query->where('id', $this->option('site'));
        }

        $sites = $query->get();

        if ($sites->isEmpty()) {
            $this->warn('Žádné sites s Fakturoid přístupovými údaji.');

            return 0;
        }

        foreach ($sites as $site) {
            $this->info("─── Syncing site: {$site->name} ───");

            try {
                $service = new FakturoidService($site);
            } catch (\Throwable $e) {
                $this->error("  Failed to connect: {$e->getMessage()}");
                Log::error('Fakturoid sync connection failed for site '.$site->id.': '.$e->getMessage());

                continue;
            }

            $this->info('  Syncing clients...');
            $clientCount = $service->pullSubjectsFromFakturoid($since);
            $this->info("  Synced {$clientCount} clients.");

            $this->info('  Syncing invoices...');
            $invoiceCount = $service->pullInvoicesFromFakturoid($since);
            $this->info("  Synced {$invoiceCount} invoices.");
        }

        return 0;
    }
}
