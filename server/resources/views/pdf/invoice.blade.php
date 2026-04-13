<!DOCTYPE html>
<html lang="cs">
<head>
	<meta charset="UTF-8">
	<style>
		* { margin: 0; padding: 0; box-sizing: border-box; }
		body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.5; padding: 25px; }
		.header { margin-bottom: 25px; }
		.header h1 { font-size: 22px; color: #1f2937; margin-bottom: 3px; }
		.header .doc-type { font-size: 12px; color: #6366f1; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
		.parties { width: 100%; margin-bottom: 25px; }
		.parties td { vertical-align: top; width: 50%; padding: 0 10px; }
		.party-label { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #6366f1; margin-bottom: 6px; }
		.party-name { font-size: 14px; font-weight: bold; color: #1f2937; margin-bottom: 4px; }
		.party-detail { font-size: 10px; color: #4b5563; line-height: 1.6; }
		.meta { width: 100%; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 4px; }
		.meta td { padding: 6px 10px; font-size: 10px; border-bottom: 1px solid #f3f4f6; }
		.meta .label { color: #6b7280; width: 40%; }
		.meta .value { font-weight: bold; color: #1f2937; }
		table.items { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
		table.items th { background-color: #f1f5f9; padding: 7px 8px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; border-bottom: 2px solid #6366f1; }
		table.items th.right, table.items td.right { text-align: right; }
		table.items td { padding: 7px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
		.totals { width: 100%; margin-top: 10px; }
		.totals td { padding: 4px 8px; font-size: 11px; }
		.totals .label { text-align: right; color: #6b7280; width: 80%; }
		.totals .value { text-align: right; font-weight: bold; }
		.totals .grand td { font-size: 14px; color: #6366f1; border-top: 2px solid #6366f1; padding-top: 8px; }
		.payment { margin-top: 25px; padding: 15px; background: #f8fafc; border-radius: 4px; border: 1px solid #e5e7eb; }
		.payment h3 { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #6366f1; margin-bottom: 8px; }
		.payment-grid { width: 100%; }
		.payment-grid td { padding: 3px 0; font-size: 10px; }
		.payment-grid .label { color: #6b7280; width: 35%; }
		.payment-grid .value { font-weight: bold; color: #1f2937; }
		.qr-section { margin-top: 20px; text-align: center; }
		.qr-section img { margin: 0 auto; }
		.qr-label { font-size: 9px; color: #6b7280; margin-top: 4px; }
		.footer { margin-top: 25px; padding-top: 10px; border-top: 1px solid #e5e7eb; font-size: 8px; color: #94a3b8; text-align: center; }
		.note { margin-top: 15px; padding: 10px; background: #fffbeb; border-radius: 4px; font-size: 10px; color: #92400e; border: 1px solid #fde68a; }
	</style>
</head>
<body>
	<div class="header">
		@php
			$types = ['invoice' => 'Faktura', 'proforma' => 'Proforma faktura', 'partial_proforma' => 'Zálohová faktura', 'final_invoice' => 'Vyúčtovací faktura'];
		@endphp
		<div class="doc-type">{{ $types[$invoice->document_type] ?? 'Faktura' }}</div>
		<h1>{{ $invoice->number ?? 'KONCEPT' }}</h1>
	</div>

	<table class="parties">
		<tr>
			<td>
				<div class="party-label">Dodavatel</div>
				@if($site)
					<div class="party-name">{{ $site->billing_name ?? $site->name }}</div>
					<div class="party-detail">
						@if($site->billing_street){{ $site->billing_street }}<br>@endif
						@if($site->billing_zip || $site->billing_city){{ $site->billing_zip }} {{ $site->billing_city }}<br>@endif
						@if($site->billing_ico)IČO: {{ $site->billing_ico }}<br>@endif
						@if($site->billing_dic)DIČ: {{ $site->billing_dic }}@endif
					</div>
				@else
					<div class="party-name">—</div>
				@endif
			</td>
			<td>
				<div class="party-label">Odběratel</div>
				@if($invoice->client)
					<div class="party-name">{{ $invoice->client->name }}</div>
					<div class="party-detail">
						@if($invoice->client->street){{ $invoice->client->street }}<br>@endif
						@if($invoice->client->zip || $invoice->client->city){{ $invoice->client->zip }} {{ $invoice->client->city }}<br>@endif
						@if($invoice->client->ico)IČO: {{ $invoice->client->ico }}<br>@endif
						@if($invoice->client->dic)DIČ: {{ $invoice->client->dic }}@endif
					</div>
				@else
					<div class="party-name">—</div>
				@endif
			</td>
		</tr>
	</table>

	<table class="meta">
		@if($invoice->issued_on)<tr><td class="label">Datum vystavení</td><td class="value">{{ $invoice->issued_on->format('d.m.Y') }}</td></tr>@endif
		@if($invoice->taxable_fulfillment_due)<tr><td class="label">Datum zdanitelného plnění</td><td class="value">{{ $invoice->taxable_fulfillment_due->format('d.m.Y') }}</td></tr>@endif
		@if($invoice->due_on)<tr><td class="label">Datum splatnosti</td><td class="value">{{ $invoice->due_on->format('d.m.Y') }}</td></tr>@endif
		@if($invoice->variable_symbol)<tr><td class="label">Variabilní symbol</td><td class="value">{{ $invoice->variable_symbol }}</td></tr>@endif
		@if($invoice->constant_symbol)<tr><td class="label">Konstantní symbol</td><td class="value">{{ $invoice->constant_symbol }}</td></tr>@endif
		@php
			$methods = ['bank' => 'Bankovní převod', 'cash' => 'Hotovost', 'card' => 'Kartou', 'paypal' => 'PayPal'];
		@endphp
		<tr><td class="label">Způsob platby</td><td class="value">{{ $methods[$invoice->payment_method] ?? $invoice->payment_method }}</td></tr>
	</table>

	@if($invoice->subject)
		<div style="margin-bottom: 15px;"><strong>Předmět:</strong> {{ $invoice->subject }}</div>
	@endif

	<table class="items">
		<thead>
			<tr>
				<th>#</th>
				<th>Název</th>
				<th class="right">Množství</th>
				<th class="right">Jedn.</th>
				<th class="right">Cena/ks</th>
				<th class="right">DPH</th>
				<th class="right">Celkem</th>
			</tr>
		</thead>
		<tbody>
			@foreach($invoice->items as $index => $item)
				<tr>
					<td>{{ $index + 1 }}</td>
					<td>{{ $item->name }}</td>
					<td class="right">{{ number_format($item->quantity, 2, ',', ' ') }}</td>
					<td class="right">{{ $item->unit_name ?? 'ks' }}</td>
					<td class="right">{{ number_format($item->unit_price, 2, ',', ' ') }}</td>
					<td class="right">{{ number_format($item->vat_rate, 0) }} %</td>
					<td class="right">{{ number_format($item->total_with_vat, 2, ',', ' ') }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>

	<table class="totals">
		<tr><td class="label">Celkem bez DPH:</td><td class="value">{{ number_format($invoice->subtotal, 2, ',', ' ') }}</td></tr>
		<tr><td class="label">DPH:</td><td class="value">{{ number_format($invoice->total_vat, 2, ',', ' ') }}</td></tr>
		<tr class="grand"><td class="label">Celkem k úhradě:</td><td class="value">{{ number_format($invoice->total, 2, ',', ' ') }} Kč</td></tr>
	</table>

	@if($site && ($site->billing_bank_account || $site->billing_iban))
		<div class="payment">
			<h3>Platební údaje</h3>
			<table class="payment-grid">
				@if($site->billing_bank_account)<tr><td class="label">Číslo účtu</td><td class="value">{{ $site->billing_bank_account }}</td></tr>@endif
				@if($site->billing_iban)<tr><td class="label">IBAN</td><td class="value">{{ $site->billing_iban }}</td></tr>@endif
				@if($site->billing_swift)<tr><td class="label">SWIFT/BIC</td><td class="value">{{ $site->billing_swift }}</td></tr>@endif
				@if($invoice->variable_symbol)<tr><td class="label">Variabilní symbol</td><td class="value">{{ $invoice->variable_symbol }}</td></tr>@endif
			</table>

			@if($site->billing_iban && $invoice->total > 0)
				@php
					// Generate SPAYD QR code
					$spayd = 'SPD*1.0';
					$spayd .= '*ACC:' . str_replace(' ', '', $site->billing_iban);
					$spayd .= '*AM:' . number_format($invoice->total, 2, '.', '');
					$spayd .= '*CC:CZK';
					if ($invoice->variable_symbol) {
						$spayd .= '*X-VS:' . $invoice->variable_symbol;
					}
					$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($spayd);
				@endphp
				<div class="qr-section">
					<img src="{{ $qrUrl }}" alt="QR platba" width="120" height="120" />
					<div class="qr-label">QR platba</div>
				</div>
			@endif
		</div>
	@endif

	@if($invoice->note)
		<div class="note"><strong>Poznámka:</strong> {{ $invoice->note }}</div>
	@endif

	<div class="footer">
		{{ $site?->billing_name ?? $site?->name ?? '' }} | Faktura {{ $invoice->number ?? '' }} | Vygenerováno {{ now()->format('d.m.Y H:i') }}
	</div>
</body>
</html>
