<?php

namespace App\Models\Shift;

use Illuminate\Database\Eloquent\Model;

class ShiftTemplate extends Model
{
	protected $table = 'shift_templates';

	protected $fillable = [
		'name', 'color', 'start_time', 'end_time', 'break_minutes', 'note',
	];

	public function shifts()
	{
		return $this->hasMany(Shift::class, 'shift_template_id', 'id');
	}
}
