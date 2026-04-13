<?php

namespace App\Services;

use App\Models\Client\Client;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoiceItem;
use App\Models\Site\Site;
use Fakturoid\FakturoidManager;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FakturoidService
{
	protected FakturoidManager $client;
	protected Site $site;

	public function __construct(Site $site)
	{
		if (!$site->hasFakturoid()) {
			throw new \RuntimeException('Site "' . $site->name . '" nemá nakonfigurované Fakturoid přístupové údaje.');
		}

		$this->site = $site;
		$this->client = new FakturoidManager(
			new HttpClient(),
			$site->fakturoid_client_id,
			$site->fakturoid_client_secret,
			'Webpulse <' . ($site->url ?? 'app') . '>'
		);
		$this->client->authClientCredentials();

		if ($site->fakturoid_slug) {
			$this->client->setAccountSlug($site->fakturoid_slug);
		} else {
			$user = $this->client->getUsersProvider()->getCurrentUser();
			$this->client->setAccountSlug($user->getBody()->accounts[0]->slug);
		}
	}

	// ─── Subjects (Clients) ────────────────────────────────────────

	public function pushClientToFakturoid(Client $client): void
	{
		$data = $this->mapClientToFakturoidSubject($client);

		try {
			if ($client->fakturoid_id) {
				$this->client->getSubjectsProvider()->update($client->fakturoid_id, $data);
			} else {
				$response = $this->client->getSubjectsProvider()->create($data);
				$subject = $response->getBody();
				$client->fakturoid_id = $subject->id;
			}

			$client->fakturoid_updated_at = now();
			$client->synced_at = now();
			$client->saveQuietly();
		} catch (\Throwable $e) {
			Log::error('Fakturoid push client error: ' . $e->getMessage(), ['client_id' => $client->id]);
			throw $e;
		}
	}

	public function pullSubjectsFromFakturoid(?Carbon $since = null): int
	{
		$params = [];
		if ($since) {
			$params['updated_since'] = $since->toIso8601String();
		}

		$synced = 0;
		$page = 1;

		do {
			$params['page'] = $page;
			$response = $this->client->getSubjectsProvider()->list($params);
			$subjects = $response->getBody();

			if (empty($subjects)) {
				break;
			}

			foreach ($subjects as $subject) {
				$this->upsertClientFromFakturoidSubject($subject);
				$synced++;
			}

			$page++;
		} while (count($subjects) >= 20);

		return $synced;
	}

	protected function upsertClientFromFakturoidSubject(object $subject): Client
	{
		$client = Client::where('fakturoid_id', $subject->id)->first();

		if ($client && $client->local_updated_at && $client->fakturoid_updated_at) {
			$remoteUpdated = Carbon::parse($subject->updated_at);
			if ($client->local_updated_at > $remoteUpdated) {
				return $client;
			}
		}

		if (!$client) {
			$client = new Client();
			$client->fakturoid_id = $subject->id;
		}

		$client->name = $subject->name ?? '';
		$client->full_name = $subject->full_name ?? null;
		$client->email = $subject->email ?? null;
		$client->email_copy = $subject->email_copy ?? null;
		$client->phone = $subject->phone ?? null;
		$client->ico = $subject->registration_no ?? null;
		$client->dic = $subject->vat_no ?? null;
		$client->web = $subject->web ?? null;
		$client->street = $subject->street ?? null;
		$client->city = $subject->city ?? null;
		$client->zip = $subject->zip ?? null;
		$client->bank_account_number = $subject->bank_account ?? null;
		$client->bank_account_iban = $subject->iban ?? null;
		$client->bank_account_swift = $subject->swift_bic ?? null;
		$client->variable_symbol = $subject->variable_symbol ?? null;
		$client->note = $subject->note ?? null;
		$client->fakturoid_updated_at = Carbon::parse($subject->updated_at);
		$client->synced_at = now();
		$client->saveQuietly();

		return $client;
	}

	protected function mapClientToFakturoidSubject(Client $client): array
	{
		return array_filter([
			'name' => $client->name,
			'full_name' => $client->full_name,
			'email' => $client->email,
			'email_copy' => $client->email_copy,
			'phone' => $client->phone,
			'registration_no' => $client->ico,
			'vat_no' => $client->dic,
			'web' => $client->web,
			'street' => $client->street,
			'city' => $client->city,
			'zip' => $client->zip,
			'bank_account' => $client->bank_account_number,
			'iban' => $client->bank_account_iban,
			'swift_bic' => $client->bank_account_swift,
			'variable_symbol' => $client->variable_symbol,
			'note' => $client->note,
		], fn($v) => $v !== null);
	}

	// ─── Invoices ──────────────────────────────────────────────────

	public function pushInvoiceToFakturoid(Invoice $invoice): void
	{
		$data = $this->mapInvoiceToFakturoidData($invoice);

		try {
			if ($invoice->fakturoid_id) {
				$this->client->getInvoicesProvider()->update($invoice->fakturoid_id, $data);
			} else {
				$response = $this->client->getInvoicesProvider()->create($data);
				$inv = $response->getBody();
				$invoice->fakturoid_id = $inv->id;
			}

			$invoice->fakturoid_updated_at = now();
			$invoice->synced_at = now();
			$invoice->saveQuietly();
		} catch (\Throwable $e) {
			Log::error('Fakturoid push invoice error: ' . $e->getMessage(), ['invoice_id' => $invoice->id]);
			throw $e;
		}
	}

	public function pullInvoicesFromFakturoid(?Carbon $since = null): int
	{
		$params = [];
		if ($since) {
			$params['updated_since'] = $since->toIso8601String();
		}

		$synced = 0;
		$page = 1;

		do {
			$params['page'] = $page;
			$response = $this->client->getInvoicesProvider()->list($params);
			$invoices = $response->getBody();

			if (empty($invoices)) {
				break;
			}

			foreach ($invoices as $inv) {
				$this->upsertInvoiceFromFakturoid($inv);
				$synced++;
			}

			$page++;
		} while (count($invoices) >= 20);

		return $synced;
	}

	protected function upsertInvoiceFromFakturoid(object $inv): Invoice
	{
		$invoice = Invoice::where('fakturoid_id', $inv->id)->first();

		if ($invoice && $invoice->local_updated_at && $invoice->fakturoid_updated_at) {
			$remoteUpdated = Carbon::parse($inv->updated_at);
			if ($invoice->local_updated_at > $remoteUpdated) {
				return $invoice;
			}
		}

		if (!$invoice) {
			$invoice = new Invoice();
			$invoice->fakturoid_id = $inv->id;
		}

		// Link to local client by fakturoid subject_id
		if (!empty($inv->subject_id)) {
			$client = Client::where('fakturoid_id', $inv->subject_id)->first();
			$invoice->client_id = $client?->id;
		}

		$invoice->number = $inv->number ?? null;
		$invoice->subject = $inv->note ?? null;
		$invoice->status = $this->mapFakturoidInvoiceStatus($inv->status ?? 'open');
		$invoice->subtotal = $inv->subtotal ?? 0;
		$invoice->total = $inv->total ?? 0;
		$invoice->total_vat = ($inv->total ?? 0) - ($inv->subtotal ?? 0);
		$invoice->payment_method = $this->mapFakturoidPaymentMethod($inv->payment_method ?? 'bank');
		$invoice->variable_symbol = $inv->variable_symbol ?? null;
		$invoice->bank_account = $inv->bank_account ?? null;
		$invoice->iban = $inv->iban ?? null;
		$invoice->swift_bic = $inv->swift_bic ?? null;
		$invoice->issued_on = !empty($inv->issued_on) ? Carbon::parse($inv->issued_on) : null;
		$invoice->taxable_fulfillment_due = !empty($inv->taxable_fulfillment_due) ? Carbon::parse($inv->taxable_fulfillment_due) : null;
		$invoice->due_on = !empty($inv->due_on) ? Carbon::parse($inv->due_on) : null;
		$invoice->paid_on = !empty($inv->paid_on) ? Carbon::parse($inv->paid_on) : null;
		$invoice->sent_on = !empty($inv->sent_at) ? Carbon::parse($inv->sent_at) : null;
		$invoice->document_type = $this->mapFakturoidDocumentType($inv->document_type ?? 'invoice');
		$invoice->fakturoid_updated_at = Carbon::parse($inv->updated_at);
		$invoice->synced_at = now();
		$invoice->saveQuietly();

		// Sync invoice lines
		if (!empty($inv->lines)) {
			$invoice->items()->delete();
			$position = 0;
			foreach ($inv->lines as $line) {
				$invoice->items()->create([
					'name' => $line->name ?? '',
					'quantity' => $line->quantity ?? 1,
					'unit_name' => $line->unit_name ?? null,
					'unit_price' => $line->unit_price ?? 0,
					'vat_rate' => $line->vat_rate ?? 0,
					'total_without_vat' => ($line->quantity ?? 1) * ($line->unit_price ?? 0),
					'total_vat' => (($line->quantity ?? 1) * ($line->unit_price ?? 0)) * (($line->vat_rate ?? 0) / 100),
					'total_with_vat' => (($line->quantity ?? 1) * ($line->unit_price ?? 0)) * (1 + ($line->vat_rate ?? 0) / 100),
					'position' => $position++,
				]);
			}
		}

		return $invoice;
	}

	protected function mapInvoiceToFakturoidData(Invoice $invoice): array
	{
		$data = array_filter([
			'number' => $invoice->number,
			'note' => $invoice->subject,
			'footer_note' => $invoice->footer_note,
			'payment_method' => $invoice->payment_method,
			'variable_symbol' => $invoice->variable_symbol,
			'bank_account' => $invoice->bank_account,
			'iban' => $invoice->iban,
			'swift_bic' => $invoice->swift_bic,
			'issued_on' => $invoice->issued_on?->format('Y-m-d'),
			'taxable_fulfillment_due' => $invoice->taxable_fulfillment_due?->format('Y-m-d'),
			'due_on' => $invoice->due_on?->format('Y-m-d'),
		], fn($v) => $v !== null);

		// Link to Fakturoid subject
		if ($invoice->client_id) {
			$client = Client::find($invoice->client_id);
			if ($client?->fakturoid_id) {
				$data['subject_id'] = $client->fakturoid_id;
			}
		}

		// Invoice lines
		$lines = [];
		foreach ($invoice->items as $item) {
			$lines[] = [
				'name' => $item->name,
				'quantity' => $item->quantity,
				'unit_name' => $item->unit_name,
				'unit_price' => $item->unit_price,
				'vat_rate' => $item->vat_rate,
			];
		}
		if (!empty($lines)) {
			$data['lines'] = $lines;
		}

		return $data;
	}

	protected function mapFakturoidInvoiceStatus(string $status): string
	{
		return match ($status) {
			'open' => 'open',
			'sent' => 'sent',
			'overdue' => 'overdue',
			'paid' => 'paid',
			'cancelled' => 'cancelled',
			default => 'open',
		};
	}

	protected function mapFakturoidPaymentMethod(string $method): string
	{
		return match ($method) {
			'bank' => 'bank',
			'cash' => 'cash',
			'card' => 'card',
			'paypal' => 'paypal',
			default => 'bank',
		};
	}

	protected function mapFakturoidDocumentType(string $type): string
	{
		return match ($type) {
			'proforma' => 'proforma',
			'partial_proforma' => 'partial_proforma',
			'final_invoice' => 'final_invoice',
			default => 'invoice',
		};
	}

	// ─── Webhooks ──────────────────────────────────────────────────

	public function handleWebhook(string $eventName, object $data): void
	{
		match (true) {
			str_starts_with($eventName, 'subject_') => $this->handleSubjectWebhook($eventName, $data),
			str_starts_with($eventName, 'invoice_') => $this->handleInvoiceWebhook($eventName, $data),
			default => Log::info('Fakturoid webhook unhandled event: ' . $eventName),
		};
	}

	protected function handleSubjectWebhook(string $event, object $data): void
	{
		if (!empty($data->subject_id)) {
			try {
				$response = $this->client->getSubjectsProvider()->get($data->subject_id);
				$subject = $response->getBody();
				$this->upsertClientFromFakturoidSubject($subject);
			} catch (\Throwable $e) {
				Log::error('Fakturoid webhook subject error: ' . $e->getMessage());
			}
		}
	}

	protected function handleInvoiceWebhook(string $event, object $data): void
	{
		if (!empty($data->invoice_id)) {
			try {
				$response = $this->client->getInvoicesProvider()->get($data->invoice_id);
				$invoice = $response->getBody();
				$this->upsertInvoiceFromFakturoid($invoice);
			} catch (\Throwable $e) {
				Log::error('Fakturoid webhook invoice error: ' . $e->getMessage());
			}
		}
	}
}
