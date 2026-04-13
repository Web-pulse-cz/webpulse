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
			font-size: 11px;
			color: #1f2937;
			line-height: 1.4;
		}
		.header {
			margin-bottom: 20px;
			border-bottom: 2px solid #6366f1;
			padding-bottom: 15px;
		}
		.header h1 {
			font-size: 22px;
			color: #6366f1;
			margin-bottom: 5px;
		}
		.filters {
			margin-bottom: 20px;
			padding: 10px;
			background-color: #f8fafc;
			border-radius: 4px;
			font-size: 10px;
			color: #64748b;
		}
		.filters span {
			margin-right: 15px;
		}
		table.entries {
			width: 100%;
			border-collapse: collapse;
			margin-bottom: 20px;
		}
		table.entries th {
			background-color: #f1f5f9;
			padding: 6px 8px;
			text-align: left;
			font-size: 9px;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			color: #64748b;
			border-bottom: 1px solid #cbd5e1;
		}
		table.entries th.right,
		table.entries td.right {
			text-align: right;
		}
		table.entries td {
			padding: 6px 8px;
			border-bottom: 1px solid #e2e8f0;
			font-size: 10px;
		}
		table.entries tr:nth-child(even) {
			background-color: #f8fafc;
		}
		.summary {
			margin-top: 15px;
			border-top: 2px solid #6366f1;
			padding-top: 10px;
		}
		.summary table {
			width: 100%;
		}
		.summary td {
			padding: 4px 8px;
		}
		.summary .label {
			text-align: right;
			color: #64748b;
			width: 80%;
			font-size: 11px;
		}
		.summary .value {
			text-align: right;
			font-weight: bold;
			font-size: 13px;
			width: 20%;
		}
		.footer {
			margin-top: 30px;
			padding-top: 10px;
			border-top: 1px solid #e2e8f0;
			font-size: 9px;
			color: #94a3b8;
		}
	</style>
</head>
<body>
	<div class="header">
		<h1>Časové záznamy</h1>
		<span style="font-size: 12px; color: #64748b;">Export ze dne {{ now()->format('d.m.Y H:i') }}</span>
	</div>

	@if($filters['date_from'] || $filters['date_to'] || $filters['project'])
		<div class="filters">
			<strong>Filtry:</strong>
			@if($filters['date_from'])
				<span>Od: {{ \Carbon\Carbon::parse($filters['date_from'])->format('d.m.Y') }}</span>
			@endif
			@if($filters['date_to'])
				<span>Do: {{ \Carbon\Carbon::parse($filters['date_to'])->format('d.m.Y') }}</span>
			@endif
			@if($filters['project'])
				<span>Projekt: {{ $filters['project'] }}</span>
			@endif
		</div>
	@endif

	<table class="entries">
		<thead>
			<tr>
				<th>Datum</th>
				<th>Projekt</th>
				<th>Úkol</th>
				<th>Uživatel</th>
				<th>Popis</th>
				<th class="right">Hodiny</th>
				<th class="right">Sazba</th>
				<th class="right">Celkem</th>
			</tr>
		</thead>
		<tbody>
			@foreach($entries as $entry)
				<tr>
					<td>{{ $entry->date?->format('d.m.Y') }}</td>
					<td>{{ $entry->project?->name ?? '—' }}</td>
					<td>
						@if($entry->task)
							<strong>{{ $entry->task->code }}</strong> {{ $entry->task->name }}
						@else
							—
						@endif
					</td>
					<td>{{ $entry->user?->name ?? '—' }}</td>
					<td>{{ $entry->description ?? '—' }}</td>
					@php $h = floor($entry->seconds / 3600); $m = floor(($entry->seconds % 3600) / 60); $s = $entry->seconds % 60; @endphp
					<td class="right">{{ sprintf('%02d:%02d:%02d', $h, $m, $s) }}</td>
					<td class="right">{{ $entry->hourly_rate ? number_format($entry->hourly_rate, 2, ',', ' ') : '—' }}</td>
					<td class="right">{{ number_format(($entry->seconds / 3600) * ($entry->hourly_rate ?? 0), 2, ',', ' ') }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>

	<div class="summary">
		<table>
			<tr>
				<td class="label">Celkem hodin:</td>
				<td class="value">{{ number_format($totalHours, 2, ',', ' ') }} h</td>
			</tr>
			<tr>
				<td class="label">Celková hodnota:</td>
				<td class="value" style="color: #6366f1;">{{ number_format($totalCost, 2, ',', ' ') }}</td>
			</tr>
		</table>
	</div>

	<div class="footer">
		Časové záznamy | Vygenerováno {{ now()->format('d.m.Y H:i') }} | Počet záznamů: {{ count($entries) }}
	</div>
</body>
</html>
