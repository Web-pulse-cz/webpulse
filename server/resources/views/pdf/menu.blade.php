<!DOCTYPE html>
<html lang="cs">
<head>
	<meta charset="UTF-8">
	<style>
		* { margin: 0; padding: 0; box-sizing: border-box; }
		body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.5; padding: 30px; }
		.header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #1f2937; }
		.header h1 { font-size: 28px; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 5px; }
		.header .subtitle { font-size: 11px; color: #64748b; letter-spacing: 1px; }
		.section { margin-bottom: 25px; }
		.section-title { font-size: 16px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; color: #1f2937; margin-bottom: 12px; padding-bottom: 6px; border-bottom: 1px solid #d1d5db; }
		.menu-item { display: table; width: 100%; margin-bottom: 10px; }
		.item-left { display: table-cell; vertical-align: top; width: 75%; }
		.item-right { display: table-cell; vertical-align: top; text-align: right; width: 25%; }
		.item-name { font-size: 12px; font-weight: bold; color: #1f2937; }
		.item-weight { font-size: 10px; color: #64748b; margin-left: 5px; }
		.item-desc { font-size: 10px; color: #64748b; margin-top: 2px; }
		.item-allergens { font-size: 9px; color: #94a3b8; margin-top: 2px; font-style: italic; }
		.item-price { font-size: 13px; font-weight: bold; color: #1f2937; }
		.dots { border-bottom: 1px dotted #d1d5db; flex: 1; margin: 0 8px; }
		.footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #d1d5db; text-align: center; font-size: 9px; color: #94a3b8; }
	</style>
</head>
<body>
	<div class="header">
		<h1>{{ $menu->name }}</h1>
		@if($menu->perex)
			<div class="subtitle">{{ $menu->perex }}</div>
		@endif
	</div>

	@foreach($sections as $section)
		<div class="section">
			<div class="section-title">{{ $section['name'] }}</div>

			@foreach($section['items'] as $item)
				<div class="menu-item">
					<div class="item-left">
						<div>
							<span class="item-name">{{ $item['name'] }}</span>
							@if($item['weight'])
								<span class="item-weight">{{ $item['weight'] }}</span>
							@endif
						</div>
						@if($item['description'])
							<div class="item-desc">{{ $item['description'] }}</div>
						@endif
						@if(!empty($item['allergens']))
							<div class="item-allergens">Alergeny: {{ implode(', ', $item['allergens']) }}</div>
						@endif
					</div>
					<div class="item-right">
						<span class="item-price">{{ number_format($item['price'], 0, ',', ' ') }} Kč</span>
					</div>
				</div>
			@endforeach
		</div>
	@endforeach

	<div class="footer">
		Jídelní lístek | {{ $menu->name }}
	</div>
</body>
</html>
