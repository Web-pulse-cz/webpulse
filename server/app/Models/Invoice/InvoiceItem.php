<?php

namespace App\Models\Invoice;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
	protected $table = 'invoice_items';

	protected $fillable = [
		'invoice_id',
		'name',
		'quantity',
		'unit_name',
		'unit_price',
		'vat_rate',
		'total_without_vat',
		'total_vat',
		'total_with_vat',
		'position',
	];

	public function invoice()
	{
		return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
	}
}
