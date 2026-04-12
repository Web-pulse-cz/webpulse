<?php

namespace App\Models\PriceOffer;

use App\Models\Client\Client;
use App\Models\Currency\Currency;
use App\Models\Invoice\Invoice;
use App\Models\Project\Project;
use App\Models\TaxRate\TaxRate;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class PriceOffer extends Model
{
	protected $table = 'price_offers';

	protected $fillable = [
		'code',
		'user_id',
		'client_id',
		'project_id',
		'title',
		'introduction',
		'note',
		'terms',
		'status',
		'currency_id',
		'tax_rate_id',
		'total_without_vat',
		'total_vat',
		'total_with_vat',
		'valid_to',
		'accepted_at',
		'rejected_at',
		'invoice_id',
	];

	protected $casts = [
		'valid_to' => 'date',
		'accepted_at' => 'datetime',
		'rejected_at' => 'datetime',
		'total_without_vat' => 'decimal:2',
		'total_vat' => 'decimal:2',
		'total_with_vat' => 'decimal:2',
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	public function client()
	{
		return $this->belongsTo(Client::class, 'client_id', 'id');
	}

	public function project()
	{
		return $this->belongsTo(Project::class, 'project_id', 'id');
	}

	public function currency()
	{
		return $this->belongsTo(Currency::class, 'currency_id', 'id');
	}

	public function taxRate()
	{
		return $this->belongsTo(TaxRate::class, 'tax_rate_id', 'id');
	}

	public function invoice()
	{
		return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
	}

	public function items()
	{
		return $this->hasMany(PriceOfferItem::class, 'price_offer_id', 'id')->orderBy('position');
	}

	public function generateCode(): string
	{
		$lastOffer = self::whereYear('created_at', date('Y'))->orderBy('id', 'desc')->first();
		if ($lastOffer && $lastOffer->code) {
			$lastNumber = (int) substr($lastOffer->code, -4);
			$newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
			return 'CN-' . date('Y') . '-' . $newNumber;
		}
		return 'CN-' . date('Y') . '-0001';
	}

	public function recalculateTotals(): void
	{
		$this->total_without_vat = $this->items()->sum('total_without_vat');
		$this->total_vat = $this->items()->sum('total_vat');
		$this->total_with_vat = $this->items()->sum('total_with_vat');
		$this->save();
	}
}
