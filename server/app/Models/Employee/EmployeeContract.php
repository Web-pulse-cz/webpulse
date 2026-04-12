<?php

namespace App\Models\Employee;

use App\Models\Currency\Currency;
use Illuminate\Database\Eloquent\Model;

class EmployeeContract extends Model
{
	protected $table = 'employee_contracts';

	protected $fillable = [
		'employee_id', 'title', 'description', 'type', 'status',
		'date_from', 'date_to', 'salary', 'salary_type', 'currency_id',
		'file_path', 'content', 'signed_by_employee', 'signed_at',
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

	public function currency()
	{
		return $this->belongsTo(Currency::class, 'currency_id', 'id');
	}
}
