<?php

namespace App\Models\TaxRate;

use App\Models\Project\Project;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use HasFactory;

    protected $table = 'tax_rates';

    protected $fillable = [
        'name',
        'rate',
    ];

    protected $casts = [
        'rate' => 'float',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class, 'tax_rate_id', 'id');
    }
}
