<?php

namespace App\Models\Cashflow;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashflowCategory extends Model
{
    use HasFactory;

    protected $table = 'cashflow_categories';

    protected $fillable = [
        'name',
        'icon',
    ];

    public function budgets()
    {
        return $this->hasMany(CashflowBudget::class, 'cashflow_category_id', 'id');
    }

    public function cashflows()
    {
        return $this->hasMany(Cashflow::class, 'cashflow_category_id', 'id');
    }
}
