<?php

namespace App\Jobs\Fakturoid;

use App\Models\Invoice\Invoice;
use App\Models\Site\Site;
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
        protected int $invoiceId,
        protected int $siteId
    ) {}

    public function handle(): void
    {
        $invoice = Invoice::find($this->invoiceId);
        $site = Site::find($this->siteId);
        if (! $invoice || ! $site || ! $site->hasFakturoid()) {
            return;
        }

        try {
            $service = new FakturoidService($site);
            $service->pushInvoiceToFakturoid($invoice);
        } catch (\Throwable $e) {
            Log::error('SyncInvoiceToFakturoidJob failed: '.$e->getMessage(), [
                'invoice_id' => $this->invoiceId,
                'site_id' => $this->siteId,
            ]);
            throw $e;
        }
    }
}
