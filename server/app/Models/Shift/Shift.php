<?php

namespace App\Models\Shift;

use App\Models\Employee\Employee;
use App\Traits\Siteable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use Siteable;

    protected $table = 'shifts';

    protected $fillable = [
        'shift_template_id', 'date', 'start_time', 'end_time',
        'break_minutes', 'location', 'note',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function template()
    {
        return $this->belongsTo(ShiftTemplate::class, 'shift_template_id', 'id');
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'shift_employee', 'shift_id', 'employee_id')
            ->withPivot('status', 'note');
    }

    public function getDurationHoursAttribute(): float
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        $totalMinutes = $start->diffInMinutes($end) - ($this->break_minutes ?? 0);

        return round($totalMinutes / 60, 2);
    }

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }
}
