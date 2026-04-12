<?php

namespace App\Models\Food\Menu;

use App\Traits\Siteable;
use Illuminate\Database\Eloquent\Model;

class MenuSection extends Model
{
	use Siteable;

	protected $table = 'menu_sections';

	protected $fillable = [
		'name',
		'description',
		'position',
	];

	public function sites()
	{
		return $this->morphToMany('App\Models\Site\Site', 'siteable');
	}

	public function menuItems()
	{
		return $this->hasMany(MenuItem::class, 'section_id', 'id')->orderBy('position');
	}
}
