<?php

namespace App\Models\Employee;

use App\Models\Country\Country;
use App\Models\Currency\Currency;
use App\Models\Shift\Shift;
use App\Traits\Fileable;
use App\Traits\Siteable;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use Fileable, Siteable;

    protected $table = 'employees';

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone_prefix', 'phone',
        'date_of_birth', 'gender', 'personal_id_number', 'id_card_number', 'nationality',
        'street', 'city', 'zip', 'country_id',
        'position', 'employee_number', 'status', 'date_hired', 'date_terminated',
        'hourly_rate', 'monthly_salary', 'currency_id',
        'bank_account_number', 'bank_account_iban', 'bank_account_swift',
        'health_insurance_company', 'health_insurance_number',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
        'photo', 'note',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_hired' => 'date',
        'date_terminated' => 'date',
        'hourly_rate' => 'decimal:2',
        'monthly_salary' => 'decimal:2',
    ];

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function divisions()
    {
        return $this->belongsToMany(EmployeeDivision::class, 'employee_division_employee', 'employee_id', 'division_id');
    }

    public function contracts()
    {
        return $this->hasMany(EmployeeContract::class, 'employee_id', 'id')->orderBy('date_from', 'desc');
    }

    public function activeContract()
    {
        return $this->hasOne(EmployeeContract::class, 'employee_id', 'id')
            ->where('status', 'active')
            ->latest('date_from');
    }

    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'shift_employee', 'employee_id', 'shift_id')
            ->withPivot('status', 'note');
    }

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }
}
