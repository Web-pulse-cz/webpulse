<?php

namespace App\Models\Voucher;

use App\Models\Currency\Currency;
use App\Models\Customer\Customer;
use App\Traits\Siteable;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use Siteable;

    protected $table = 'vouchers';

    protected $fillable = [
        'code', 'name', 'description',
        'discount_type', 'discount_value', 'currency_id',
        'min_order_value', 'valid_from', 'valid_to',
        'max_uses', 'used_count', 'max_uses_per_customer',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'voucher_customer', 'voucher_id', 'customer_id')
            ->withPivot('times_used', 'last_used_at');
    }

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->valid_from && now()->lt($this->valid_from)) {
            return false;
        }
        if ($this->valid_to && now()->gt($this->valid_to)) {
            return false;
        }
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }
}
