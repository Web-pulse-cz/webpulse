<?php

namespace App\Models\Client;

use App\Models\Country\Country;
use App\Models\Invoice\Invoice;
use App\Models\PriceOffer\PriceOffer;
use App\Models\Project\Project;
use App\Traits\Siteable;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use Siteable;

    protected $table = 'clients';

    protected $fillable = [
        'fakturoid_id',
        'type',
        'name',
        'full_name',
        'email',
        'email_copy',
        'phone_prefix',
        'phone',
        'ico',
        'dic',
        'web',
        'street',
        'city',
        'zip',
        'country_id',
        'has_delivery_address',
        'delivery_name',
        'delivery_street',
        'delivery_city',
        'delivery_zip',
        'delivery_country_id',
        'bank_account_number',
        'bank_account_iban',
        'bank_account_swift',
        'variable_symbol',
        'note',
        'fakturoid_updated_at',
        'local_updated_at',
        'synced_at',
    ];

    protected $casts = [
        'has_delivery_address' => 'boolean',
        'fakturoid_updated_at' => 'datetime',
        'local_updated_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function deliveryCountry()
    {
        return $this->belongsTo(Country::class, 'delivery_country_id', 'id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'client_id', 'id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'client_id', 'id');
    }

    public function priceOffers()
    {
        return $this->hasMany(PriceOffer::class, 'client_id', 'id');
    }

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }
}
