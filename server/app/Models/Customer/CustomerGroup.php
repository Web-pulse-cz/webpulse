<?php

namespace App\Models\Customer;

use App\Models\Currency\Currency;
use App\Traits\Siteable;
use Illuminate\Database\Eloquent\Model;

class CustomerGroup extends Model
{
    use Siteable;

    protected $table = 'customer_groups';

    protected $fillable = [
        'name', 'description', 'color', 'discount_type',
        'discount_value', 'discount_currency_id', 'position',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
    ];

    public function discountCurrency()
    {
        return $this->belongsTo(Currency::class, 'discount_currency_id', 'id');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'customer_group_id', 'id');
    }

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }
}
