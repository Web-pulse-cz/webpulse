<!DOCTYPE html>
<html lang="cs">
<head>
	<meta charset="UTF-8">
	<style>
		* { margin: 0; padding: 0; box-sizing: border-box; }
		body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.6; padding: 30px; }
		.header { margin-bottom: 25px; border-bottom: 2px solid #6366f1; padding-bottom: 15px; }
		.header .doc-type { font-size: 10px; color: #6366f1; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
		.header h1 { font-size: 20px; color: #1f2937; margin-top: 4px; }
		.meta { width: 100%; margin-bottom: 25px; }
		.meta td { padding: 4px 10px; font-size: 10px; vertical-align: top; }
		.meta .label { color: #6b7280; width: 30%; }
		.meta .value { font-weight: bold; color: #1f2937; }
		.parties { width: 100%; margin-bottom: 25px; }
		.parties td { vertical-align: top; width: 50%; padding: 0 10px; }
		.party-label { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #6366f1; margin-bottom: 6px; }
		.party-name { font-size: 13px; font-weight: bold; color: #1f2937; margin-bottom: 4px; }
		.party-detail { font-size: 10px; color: #4b5563; line-height: 1.6; }
		.content { margin: 20px 0; font-size: 11px; line-height: 1.8; }
		.content h1 { font-size: 16px; margin: 15px 0 8px; }
		.content h2 { font-size: 14px; margin: 12px 0 6px; }
		.content h3 { font-size: 12px; margin: 10px 0 5px; }
		.content p { margin-bottom: 8px; }
		.content ul, .content ol { margin: 8px 0 8px 25px; }
		.content li { margin-bottom: 3px; }
		.section { margin-top: 20px; padding: 12px; background: #f8fafc; border-radius: 4px; border: 1px solid #e5e7eb; }
		.section h3 { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #6366f1; margin-bottom: 6px; }
		.section-text { font-size: 10px; color: #4b5563; line-height: 1.6; }
		.signatures { width: 100%; margin-top: 50px; }
		.signatures td { width: 50%; text-align: center; padding: 0 30px; vertical-align: bottom; }
		.signature-line { border-top: 1px solid #1f2937; padding-top: 6px; font-size: 10px; color: #4b5563; margin-top: 50px; }
		.footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #e5e7eb; font-size: 8px; color: #94a3b8; text-align: center; }
	</style>
</head>
<body>
	<div class="header">
		@php
			$types = [
				'hpp' => 'Pracovní smlouva (HPP)',
				'dpp' => 'Dohoda o provedení práce (DPP)',
				'dpc' => 'Dohoda o pracovní činnosti (DPČ)',
				'osvc' => 'Smlouva s OSVČ',
				'internship' => 'Smlouva o stáži',
				'nda' => 'Dohoda o mlčenlivosti (NDA)',
				'other' => 'Smlouva',
			];
		@endphp
		<div class="doc-type">{{ $types[$contract->type] ?? 'Smlouva' }}</div>
		<h1>{{ $contract->title }}</h1>
	</div>

	@if($site || $contract->employee)
	<table class="parties">
		<tr>
			<td>
				@if($site)
				<div class="party-label">Strana 1</div>
				<div class="party-name">{{ $site->billing_name ?? $site->name }}</div>
				<div class="party-detail">
					@if($site->billing_street){{ $site->billing_street }}<br>@endif
					@if($site->billing_zip || $site->billing_city){{ $site->billing_zip }} {{ $site->billing_city }}<br>@endif
					@if($site->billing_ico)IČO: {{ $site->billing_ico }}<br>@endif
					@if($site->billing_dic)DIČ: {{ $site->billing_dic }}<br>@endif
				</div>
				@endif
			</td>
			<td>
				@if($contract->employee)
				<div class="party-label">Strana 2</div>
				<div class="party-name">{{ $contract->employee->full_name }}</div>
				<div class="party-detail">
					@if($contract->employee->street){{ $contract->employee->street }}<br>@endif
					@if($contract->employee->zip || $contract->employee->city){{ $contract->employee->zip }} {{ $contract->employee->city }}<br>@endif
					@if($contract->employee->personal_id_number)RČ: {{ $contract->employee->personal_id_number }}<br>@endif
				</div>
				@endif
			</td>
		</tr>
	</table>
	@endif

	<table class="meta">
		@if($contract->date_from)
		<tr>
			<td class="label">Platnost od:</td>
			<td class="value">{{ $contract->date_from->format('d. m. Y') }}</td>
		</tr>
		@endif
		@if($contract->date_to)
		<tr>
			<td class="label">Platnost do:</td>
			<td class="value">{{ $contract->date_to->format('d. m. Y') }}</td>
		</tr>
		@endif
		@if($contract->salary > 0)
		<tr>
			<td class="label">Odměna:</td>
			<td class="value">{{ number_format($contract->salary, 2, ',', ' ') }} {{ $contract->salary_type === 'hourly' ? '/ hod' : '/ měs' }}</td>
		</tr>
		@endif
		@if($contract->vacation_days)
		<tr>
			<td class="label">Dovolená:</td>
			<td class="value">{{ $contract->vacation_days }} dní</td>
		</tr>
		@endif
		@if($contract->notice_period_days)
		<tr>
			<td class="label">Výpovědní lhůta:</td>
			<td class="value">{{ $contract->notice_period_days }} dní</td>
		</tr>
		@endif
	</table>

	@if($contract->content)
	<div class="content">
		{!! $contract->content !!}
	</div>
	@endif

	@if($contract->terms)
	<div class="section">
		<h3>Podmínky</h3>
		<div class="section-text">{!! nl2br(e($contract->terms)) !!}</div>
	</div>
	@endif

	@if($contract->benefits)
	<div class="section">
		<h3>Benefity</h3>
		<div class="section-text">{!! nl2br(e($contract->benefits)) !!}</div>
	</div>
	@endif

	<table class="signatures">
		<tr>
			<td>
				<div class="signature-line">
					@if($site){{ $site->billing_name ?? $site->name }}@else Strana 1 @endif
				</div>
			</td>
			<td>
				<div class="signature-line">
					@if($contract->employee){{ $contract->employee->full_name }}@else Strana 2 @endif
					@if($contract->signed_at)<br><small>Podepsáno: {{ $contract->signed_at->format('d. m. Y') }}</small>@endif
				</div>
			</td>
		</tr>
	</table>

	<div class="footer">
		Vygenerováno {{ now()->format('d. m. Y H:i') }}
	</div>
</body>
</html>
