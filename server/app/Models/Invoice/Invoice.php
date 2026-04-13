<?php

namespace App\Models\Invoice;

use App\Models\Client\Client;
use App\Models\Currency\Currency;
use App\Models\Language\Language;
use App\Models\PriceOffer\PriceOffer;
use App\Models\Project\Project;
use App\Traits\Siteable;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
	use Siteable;

	protected $table = 'invoices';

	protected $fillable = [
		'fakturoid_id',
		'client_id',
		'project_id',
		'price_offer_id',
		'document_type',
		'number',
		'subject',
		'note',
		'footer_note',
		'status',
		'subtotal',
		'total',
		'total_vat',
		'currency_id',
		'language_id',
		'payment_method',
		'variable_symbol',
		'constant_symbol',
		'specific_symbol',
		'bank_account',
		'iban',
		'swift_bic',
		'issued_on',
		'taxable_fulfillment_due',
		'due_on',
		'paid_on',
		'cancelled_on',
		'sent_on',
		'fakturoid_updated_at',
		'local_updated_at',
		'synced_at',
	];

	protected $casts = [
		'issued_on' => 'date',
		'taxable_fulfillment_due' => 'date',
		'due_on' => 'date',
		'paid_on' => 'date',
		'cancelled_on' => 'date',
		'sent_on' => 'date',
		'fakturoid_updated_at' => 'datetime',
		'local_updated_at' => 'datetime',
		'synced_at' => 'datetime',
	];

	public function client()
	{
		return $this->belongsTo(Client::class, 'client_id', 'id');
	}

	public function project()
	{
		return $this->belongsTo(Project::class, 'project_id', 'id');
	}

	public function priceOffer()
	{
		return $this->belongsTo(PriceOffer::class, 'price_offer_id', 'id');
	}

	public function currency()
	{
		return $this->belongsTo(Currency::class, 'currency_id', 'id');
	}

	public function language()
	{
		return $this->belongsTo(Language::class, 'language_id', 'id');
	}

	public function items()
	{
		return $this->hasMany(InvoiceItem::class, 'invoice_id', 'id')->orderBy('position');
	}

	public function sites()
	{
		return $this->morphToMany('App\Models\Site\Site', 'siteable');
	}
}
