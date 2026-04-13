<?php

namespace App\Models\Site;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
	protected $table = 'sites';

	protected $fillable = [
		'name',
		'url',
		'hash',
		'is_secure',
		'is_active',
		'settings',
		'user_id',
		'fakturoid_client_id',
		'fakturoid_client_secret',
		'fakturoid_slug',
	];

	protected $casts = [
		'is_secure' => 'boolean',
		'is_active' => 'boolean',
		'settings' => 'json',
		'fakturoid_client_secret' => 'encrypted',
	];

	protected $hidden = [
		'fakturoid_client_secret',
	];

	public function users()
	{
		return $this->belongsToMany(User::class, 'sites_has_users', 'site_id');
	}

	public function hasFakturoid(): bool
	{
		return !empty($this->fakturoid_client_id) && !empty($this->fakturoid_client_secret);
	}
}
