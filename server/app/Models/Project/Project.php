<?php

namespace App\Models\Project;

use App\Models\Client\Client;
use App\Models\Currency\Currency;
use App\Models\Invoice\Invoice;
use App\Models\TaxRate\TaxRate;
use App\Models\User\User;
use App\Traits\Siteable;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
	use Siteable;

	protected $table = 'projects';

	protected $fillable = [
		'name',
		'prefix',
		'description',
		'note',
		'image',
		'client_id',
		'status_id',
		'currency_id',
		'tax_rate_id',
		'start_date',
		'deadline_date',
		'end_date',
		'hourly_rate',
		'expected_hours',
		'total_tracked_hours',
		'expected_revenue',
		'total_revenue',
		'total_costs',
		'profit',
		'is_archived',
	];

	protected $casts = [
		'start_date' => 'date',
		'deadline_date' => 'date',
		'end_date' => 'date',
		'is_archived' => 'boolean',
		'hourly_rate' => 'decimal:2',
		'expected_hours' => 'decimal:2',
		'total_tracked_hours' => 'decimal:2',
		'expected_revenue' => 'decimal:2',
		'total_revenue' => 'decimal:2',
		'total_costs' => 'decimal:2',
		'profit' => 'decimal:2',
	];

	public function client()
	{
		return $this->belongsTo(Client::class, 'client_id', 'id');
	}

	public function status()
	{
		return $this->belongsTo(ProjectStatus::class, 'status_id', 'id');
	}

	public function currency()
	{
		return $this->belongsTo(Currency::class, 'currency_id', 'id');
	}

	public function taxRate()
	{
		return $this->belongsTo(TaxRate::class, 'tax_rate_id', 'id');
	}

	public function tags()
	{
		return $this->belongsToMany(Tag::class, 'project_tag', 'project_id', 'tag_id');
	}

	public function milestones()
	{
		return $this->hasMany(ProjectMilestone::class, 'project_id', 'id')->orderBy('position');
	}

	public function tasks()
	{
		return $this->hasMany(ProjectTask::class, 'project_id', 'id')->orderBy('position');
	}

	public function timeEntries()
	{
		return $this->hasMany(ProjectTimeEntry::class, 'project_id', 'id')->orderBy('date', 'desc');
	}

	public function costs()
	{
		return $this->hasMany(ProjectCost::class, 'project_id', 'id')->orderBy('date', 'desc');
	}

	public function notes()
	{
		return $this->hasMany(ProjectNote::class, 'project_id', 'id')->orderBy('created_at', 'desc');
	}

	public function invoices()
	{
		return $this->hasMany(Invoice::class, 'project_id', 'id');
	}

	public function taskCategories()
	{
		return $this->hasMany(ProjectTaskCategory::class, 'project_id', 'id')->orderBy('position');
	}

	public function taskBoards()
	{
		return $this->hasMany(ProjectTaskBoard::class, 'project_id', 'id')->orderBy('position');
	}

	public function sites()
	{
		return $this->morphToMany('App\Models\Site\Site', 'siteable');
	}
}
