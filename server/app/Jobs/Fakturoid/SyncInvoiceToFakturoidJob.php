<?php

namespace App\Jobs\Fakturoid;

use App\Models\Invoice\Invoice;
use App\Services\FakturoidService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncInvoiceToFakturoidJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public int $tries = 3;
	public int $backoff = 30;

	public function __construct(
		protected int $invoiceId
	) {}

	public function handle(): void
	{
		$invoice = Invoice::find($this->invoiceId);
		if (!$invoice) {
			return;
		}

		try {
			$service = new FakturoidService();
			$service->pushInvoiceToFakturoid($invoice);
		} catch (\Throwable $e) {
			Log::error('SyncInvoiceToFakturoidJob failed: ' . $e->getMessage(), [
				'invoice_id' => $this->invoiceId,
			]);
			throw $e;
		}
	}
}
