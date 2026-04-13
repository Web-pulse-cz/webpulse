<!DOCTYPE html>
<html lang="cs">
<head>
	<meta charset="UTF-8">
	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}
		body {
			font-family: DejaVu Sans, sans-serif;
			font-size: 12px;
			color: #1f2937;
			line-height: 1.5;
		}
		.header {
			margin-bottom: 30px;
			border-bottom: 2px solid #6366f1;
			padding-bottom: 20px;
		}
		.header h1 {
			font-size: 24px;
			color: #6366f1;
			margin-bottom: 5px;
		}
		.header .code {
			font-size: 14px;
			color: #6b7280;
		}
		.meta-table {
			width: 100%;
			margin-bottom: 25px;
		}
		.meta-table td {
			vertical-align: top;
			width: 50%;
			padding: 5px 0;
		}
		.meta-label {
			color: #6b7280;
			font-size: 11px;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}
		.meta-value {
			font-size: 13px;
			font-weight: bold;
		}
		.section-title {
			font-size: 14px;
			font-weight: bold;
			color: #374151;
			margin-bottom: 10px;
			margin-top: 20px;
		}
		.content-block {
			margin-bottom: 15px;
			font-size: 12px;
			color: #4b5563;
		}
		table.items {
			width: 100%;
			border-collapse: collapse;
			margin-top: 10px;
			margin-bottom: 20px;
		}
		table.items th {
			background-color: #f3f4f6;
			padding: 8px 10px;
			text-align: left;
			font-size: 11px;
			text-transform: uppercase;
			color: #6b7280;
			border-bottom: 1px solid #d1d5db;
		}
		table.items th.right,
		table.items td.right {
			text-align: right;
		}
		table.items td {
			padding: 8px 10px;
			border-bottom: 1px solid #e5e7eb;
		}
		table.items td.desc {
			font-size: 11px;
			color: #6b7280;
		}
		.totals {
			width: 100%;
			margin-top: 10px;
		}
		.totals td {
			padding: 5px 10px;
		}
		.totals .label {
			text-align: right;
			color: #6b7280;
			width: 80%;
		}
		.totals .value {
			text-align: right;
			font-weight: bold;
			width: 20%;
		}
		.totals .grand-total td {
			font-size: 16px;
			color: #6366f1;
			border-top: 2px solid #6366f1;
			padding-top: 10px;
		}
		.footer {
			margin-top: 40px;
			padding-top: 15px;
			border-top: 1px solid #e5e7eb;
			font-size: 11px;
			color: #9ca3af;
		}
	</style>
</head>
<body>
	<div class="header">
		<h1>Cenová nabídka</h1>
		<span class="code">{{ $offer->code }}</span>
	</div>

	<table class="meta-table">
		<tr>
			<td>
				<div class="meta-label">Klient</div>
				<div class="meta-value">{{ $offer->client?->name ?? '—' }}</div>
				@if($offer->client?->street)
					<div>{{ $offer->client->street }}</div>
				@endif
				@if($offer->client?->city || $offer->client?->zip)
					<div>{{ $offer->client->zip }} {{ $offer->client->city }}</div>
				@endif
				@if($offer->client?->ico)
					<div>IČO: {{ $offer->client->ico }}</div>
				@endif
				@if($offer->client?->dic)
					<div>DIČ: {{ $offer->client->dic }}</div>
				@endif
			</td>
			<td>
				@if($offer->title)
					<div class="meta-label">Název</div>
					<div class="meta-value">{{ $offer->title }}</div>
				@endif
				<div class="meta-label" style="margin-top: 8px;">Datum vytvoření</div>
				<div>{{ $offer->created_at?->format('d.m.Y') }}</div>
				@if($offer->valid_to)
					<div class="meta-label" style="margin-top: 8px;">Platnost do</div>
					<div>{{ $offer->valid_to->format('d.m.Y') }}</div>
				@endif
			</td>
		</tr>
	</table>

	@if($offer->introduction)
		<div class="section-title">Úvod</div>
		<div class="content-block">{!! nl2br(e($offer->introduction)) !!}</div>
	@endif

	<div class="section-title">Položky</div>
	<table class="items">
		<thead>
			<tr>
				<th>#</th>
				<th>Název</th>
				<th class="right">Množství</th>
				<th class="right">Cena/ks</th>
				<th class="right">DPH %</th>
				<th class="right">Celkem bez DPH</th>
			</tr>
		</thead>
		<tbody>
			@foreach($offer->items as $index => $item)
				<tr>
					<td>{{ $index + 1 }}</td>
					<td>
						{{ $item->name }}
						@if($item->description)
							<br><span class="desc">{{ $item->description }}</span>
						@endif
					</td>
					<td class="right">{{ number_format($item->quantity, 2, ',', ' ') }} {{ $item->unit_name }}</td>
					<td class="right">{{ number_format($item->unit_price_without_vat, 2, ',', ' ') }}</td>
					<td class="right">{{ number_format($item->vat_rate, 0) }} %</td>
					<td class="right">{{ number_format($item->total_without_vat, 2, ',', ' ') }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>

	<table class="totals">
		<tr>
			<td class="label">Celkem bez DPH:</td>
			<td class="value">{{ number_format($offer->total_without_vat, 2, ',', ' ') }}</td>
		</tr>
		<tr>
			<td class="label">DPH:</td>
			<td class="value">{{ number_format($offer->total_vat, 2, ',', ' ') }}</td>
		</tr>
		<tr class="grand-total">
			<td class="label">Celkem s DPH:</td>
			<td class="value">{{ number_format($offer->total_with_vat, 2, ',', ' ') }}</td>
		</tr>
	</table>

	@if($offer->terms)
		<div class="section-title">Obchodní podmínky</div>
		<div class="content-block">{!! nl2br(e($offer->terms)) !!}</div>
	@endif

	@if($offer->note)
		<div class="section-title">Poznámka</div>
		<div class="content-block">{!! nl2br(e($offer->note)) !!}</div>
	@endif

	<div class="footer">
		Cenová nabídka {{ $offer->code }} | Vygenerováno {{ now()->format('d.m.Y H:i') }}
	</div>
</body>
</html>
