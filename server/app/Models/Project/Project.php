<?php

namespace App\Models\Project;

use App\Models\Contact\Contact;
use App\Models\Country\Country;
use App\Models\Currency\Currency;
use App\Models\TaxRate\TaxRate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';

    protected $fillable = [
        'name',
        'description',
        'note',
        'image',
        'hourly_rate',
        'expected_price',
        'expected_price_vat',
        'expected_hours',
        'total_price',
        'total_price_vat',
        'total_hours',
        'currency_id',
        'invoice_firstname',
        'invoice_lastname',
        'invoice_ico',
        'invoice_dic',
        'invoice_email',
        'invoice_phone_prefix',
        'invoice_phone',
        'invoice_street',
        'invoice_city',
        'invoice_zip',
        'invoice_country_id',
        'is_delivery_address_same',
        'delivery_firstname',
        'delivery_lastname',
        'delivery_email',
        'delivery_phone_prefix',
        'delivery_phone',
        'delivery_street',
        'delivery_city',
        'delivery_zip',
        'delivery_country_id',
        'status_id',
        'tax_rate_id',
        'client_id',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'expected_price' => 'decimal:2',
        'expected_price_vat' => 'decimal:2',
        'total_price' => 'decimal:2',
        'total_price_vat' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_delivery_address_same' => 'boolean',
    ];

    protected $with = [
        'status',
        'taxRate',
        'currency',
        'client',
        'events',
    ];

    public function status()
    {
        return $this->belongsTo(ProjectStatus::class, 'status_id', 'id');
    }

    public function taxRate()
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id', 'id');
    }

    public function invoiceCountry()
    {
        return $this->belongsTo(Country::class, 'invoice_country_id', 'id');
    }

    public function deliveryCountry()
    {
        return $this->belongsTo(Country::class, 'delivery_country_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function client()
    {
        return $this->belongsTo(Contact::class, 'client_id', 'id');
    }

    public function events()
    {
        return $this->hasMany(ProjectEvent::class, 'project_id', 'id');
    }
}
