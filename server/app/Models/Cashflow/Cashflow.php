<?php

namespace App\Models\Cashflow;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cashflow extends Model
{
    use HasFactory;

    protected $table = 'cashflows';

    protected $fillable = [
        'cashflow_category_id',
        'user_id',
        'amount',
        'type',
        'description',
        'date',
        'is_repeated'
    ];

    protected $casts = [
        'date' => 'date',
        'is_repeated' => 'boolean'
    ];

    public function category()
    {
        return $this->belongsTo(CashflowCategory::class, 'cashflow_category_id', 'id');
    }
}
