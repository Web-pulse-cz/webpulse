<!DOCTYPE html>
<html lang="cs">
<head>
	<meta charset="UTF-8">
	<style>
		* { margin: 0; padding: 0; box-sizing: border-box; }
		body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.6; }
		.header { margin-bottom: 30px; border-bottom: 2px solid #6366f1; padding-bottom: 15px; }
		.header h1 { font-size: 22px; color: #6366f1; margin-bottom: 5px; }
		.header .subtitle { font-size: 12px; color: #64748b; }
		.section { margin-bottom: 25px; }
		.section h2 { font-size: 14px; font-weight: bold; color: #374151; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px solid #e5e7eb; }
		.info-grid { width: 100%; margin-bottom: 10px; }
		.info-grid td { padding: 4px 8px; vertical-align: top; }
		.info-grid .label { color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; width: 35%; }
		.info-grid .value { font-weight: bold; color: #1f2937; }
		.content-block { margin: 15px 0; padding: 15px; background: #f8fafc; border-radius: 4px; font-size: 11px; line-height: 1.8; }
		.signature-block { margin-top: 60px; }
		.signature-row { display: table; width: 100%; }
		.signature-col { display: table-cell; width: 45%; padding: 20px 0; }
		.signature-line { border-top: 1px solid #1f2937; margin-top: 60px; padding-top: 5px; font-size: 10px; color: #64748b; }
		.footer { margin-top: 40px; padding-top: 10px; border-top: 1px solid #e5e7eb; font-size: 9px; color: #94a3b8; }
	</style>
</head>
<body>
	<div class="header">
		<h1>{{ $contract->title }}</h1>
		<div class="subtitle">
			@php
				$types = ['hpp' => 'Hlavní pracovní poměr', 'dpp' => 'Dohoda o provedení práce', 'dpc' => 'Dohoda o pracovní činnosti', 'osvc' => 'OSVČ', 'internship' => 'Stáž', 'other' => 'Jiný'];
			@endphp
			{{ $types[$contract->type] ?? $contract->type }}
		</div>
	</div>

	<div class="section">
		<h2>Údaje zaměstnance</h2>
		<table class="info-grid">
			<tr><td class="label">Jméno a příjmení</td><td class="value">{{ $employee->first_name }} {{ $employee->last_name }}</td></tr>
			@if($employee->date_of_birth)<tr><td class="label">Datum narození</td><td class="value">{{ $employee->date_of_birth->format('d.m.Y') }}</td></tr>@endif
			@if($employee->personal_id_number)<tr><td class="label">Rodné číslo</td><td class="value">{{ $employee->personal_id_number }}</td></tr>@endif
			@if($employee->street)<tr><td class="label">Adresa</td><td class="value">{{ $employee->street }}, {{ $employee->zip }} {{ $employee->city }}</td></tr>@endif
			@if($employee->id_card_number)<tr><td class="label">Číslo OP</td><td class="value">{{ $employee->id_card_number }}</td></tr>@endif
		</table>
	</div>

	<div class="section">
		<h2>Podmínky smlouvy</h2>
		<table class="info-grid">
			<tr><td class="label">Pracovní pozice</td><td class="value">{{ $employee->position ?? '—' }}</td></tr>
			<tr><td class="label">Platnost od</td><td class="value">{{ $contract->date_from?->format('d.m.Y') ?? '—' }}</td></tr>
			<tr><td class="label">Platnost do</td><td class="value">{{ $contract->date_to?->format('d.m.Y') ?? 'Doba neurčitá' }}</td></tr>
			<tr>
				<td class="label">Odměna</td>
				<td class="value">
					{{ number_format($contract->salary, 2, ',', ' ') }}
					{{ $contract->salary_type === 'monthly' ? '/ měsíc' : '/ hodina' }}
				</td>
			</tr>
			<tr><td class="label">Dovolená</td><td class="value">{{ $contract->vacation_days }} dní / rok</td></tr>
			<tr><td class="label">Výpovědní lhůta</td><td class="value">{{ $contract->notice_period_days }} dní</td></tr>
		</table>
	</div>

	@if($contract->content)
	<div class="section">
		<h2>Obsah smlouvy</h2>
		<div class="content-block">{!! nl2br(e($contract->content)) !!}</div>
	</div>
	@endif

	@if($contract->terms)
	<div class="section">
		<h2>Podmínky</h2>
		<div class="content-block">{!! nl2br(e($contract->terms)) !!}</div>
	</div>
	@endif

	@if($contract->benefits)
	<div class="section">
		<h2>Benefity</h2>
		<div class="content-block">{!! nl2br(e($contract->benefits)) !!}</div>
	</div>
	@endif

	@if($contract->note)
	<div class="section">
		<h2>Poznámka</h2>
		<div class="content-block">{!! nl2br(e($contract->note)) !!}</div>
	</div>
	@endif

	<div class="signature-block">
		<table style="width: 100%;">
			<tr>
				<td style="width: 45%;">
					<div class="signature-line">Zaměstnavatel</div>
				</td>
				<td style="width: 10%;"></td>
				<td style="width: 45%;">
					<div class="signature-line">Zaměstnanec: {{ $employee->first_name }} {{ $employee->last_name }}</div>
				</td>
			</tr>
		</table>
	</div>

	<div class="footer">
		Smlouva vygenerována {{ now()->format('d.m.Y H:i') }}
	</div>
</body>
</html>
