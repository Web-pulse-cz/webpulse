<?php

namespace App\Models\Project;

use App\Models\Currency\Currency;
use Illuminate\Database\Eloquent\Model;

class ProjectCost extends Model
{
    protected $table = 'project_costs';

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'category',
        'amount',
        'currency_id',
        'date',
        'invoice_number',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }
}
