<?php

namespace App\Models\Contract;

use App\Models\Currency\Currency;
use App\Models\Employee\Employee;
use App\Models\Project\Project;
use App\Traits\Fileable;
use App\Traits\Siteable;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use Siteable, Fileable;

    protected $table = 'employee_contracts';

    protected $fillable = [
        'employee_id', 'project_id', 'title', 'description', 'type', 'status',
        'date_from', 'date_to', 'salary', 'salary_type', 'currency_id',
        'content', 'signed_by_employee', 'signed_at',
        'terms', 'benefits', 'vacation_days', 'notice_period_days', 'note',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'signed_at' => 'date',
        'salary' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }
}
