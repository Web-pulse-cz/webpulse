<?php

namespace App\Models\Employee;

use Illuminate\Database\Eloquent\Model;

class EmployeeDivision extends Model
{
    protected $table = 'employee_divisions';

    protected $fillable = [
        'name', 'description', 'color', 'address', 'city', 'zip',
        'phone', 'email', 'head_employee_id', 'position',
    ];

    public function headEmployee()
    {
        return $this->belongsTo(Employee::class, 'head_employee_id', 'id');
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_division_employee', 'division_id', 'employee_id');
    }
}
