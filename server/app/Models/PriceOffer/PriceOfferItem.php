<?php

namespace App\Models\PriceOffer;

use Illuminate\Database\Eloquent\Model;

class PriceOfferItem extends Model
{
	protected $table = 'price_offer_items';

	protected $fillable = [
		'price_offer_id',
		'name',
		'description',
		'quantity',
		'unit_name',
		'unit_price_without_vat',
		'vat_rate',
		'total_without_vat',
		'total_vat',
		'total_with_vat',
		'position',
	];

	public function priceOffer()
	{
		return $this->belongsTo(PriceOffer::class, 'price_offer_id', 'id');
	}
}
