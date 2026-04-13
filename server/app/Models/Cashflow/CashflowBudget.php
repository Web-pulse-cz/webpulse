<?php

namespace App\Models\Cashflow;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashflowBudget extends Model
{
    use HasFactory;

    protected $table = 'cashflow_budgets';

    protected $fillable = [
        'cashflow_category_id',
        'type',
        'amount',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(CashflowCategory::class, 'cashflow_category_id', 'id');
    }
}
