<?php

namespace App\Models\Customer;

use App\Models\Country\Country;
use App\Models\Currency\Currency;
use App\Models\Voucher\Voucher;
use App\Traits\Siteable;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use Siteable;

    protected $table = 'customers';

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone_prefix', 'phone',
        'date_of_birth', 'gender',
        'street', 'city', 'zip', 'country_id',
        'company_name', 'ico', 'dic',
        'total_spent', 'credit_balance', 'currency_id',
        'rating', 'customer_group_id', 'status',
        'note', 'last_purchase_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'total_spent' => 'decimal:2',
        'credit_balance' => 'decimal:2',
        'last_purchase_at' => 'datetime',
    ];

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id', 'id');
    }

    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class, 'voucher_customer', 'customer_id', 'voucher_id')
            ->withPivot('times_used', 'last_used_at');
    }

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }
}
