<?php

namespace App\Jobs\Fakturoid;

use App\Models\Client\Client;
use App\Services\FakturoidService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncClientToFakturoidJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public int $tries = 3;
	public int $backoff = 30;

	public function __construct(
		protected int $clientId
	) {}

	public function handle(): void
	{
		$client = Client::find($this->clientId);
		if (!$client) {
			return;
		}

		try {
			$service = new FakturoidService();
			$service->pushClientToFakturoid($client);
		} catch (\Throwable $e) {
			Log::error('SyncClientToFakturoidJob failed: ' . $e->getMessage(), [
				'client_id' => $this->clientId,
			]);
			throw $e;
		}
	}
}
