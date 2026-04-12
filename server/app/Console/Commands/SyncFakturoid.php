<?php

namespace App\Console\Commands;

use App\Services\FakturoidService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SyncFakturoid extends Command
{
	protected $signature = 'fakturoid:sync {--full : Run full sync instead of incremental}';

	protected $description = 'Sync clients and invoices with Fakturoid';

	public function handle(): int
	{
		try {
			$service = new FakturoidService();
		} catch (\Throwable $e) {
			$this->error('Failed to connect to Fakturoid: ' . $e->getMessage());
			Log::error('Fakturoid sync connection failed: ' . $e->getMessage());
			return 1;
		}

		$since = $this->option('full') ? null : now()->subMinutes(20);

		$this->info('Syncing clients from Fakturoid...');
		$clientCount = $service->pullSubjectsFromFakturoid($since);
		$this->info("Synced {$clientCount} clients.");

		$this->info('Syncing invoices from Fakturoid...');
		$invoiceCount = $service->pullInvoicesFromFakturoid($since);
		$this->info("Synced {$invoiceCount} invoices.");

		return 0;
	}
}
