<?php

namespace App\Models\Food\Menu;

use App\Models\Food\Allergen\Allergen;
use App\Models\Food\Meal\Meal;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $table = 'menu_items';

    protected $fillable = [
        'menu_id',
        'section_id',
        'meal_id',
        'name',
        'description',
        'price',
        'weight',
        'allergen_ids',
        'position',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'allergen_ids' => 'array',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo(MenuSection::class, 'section_id', 'id');
    }

    public function meal()
    {
        return $this->belongsTo(Meal::class, 'meal_id', 'id');
    }

    public function getAllergensAttribute()
    {
        if (empty($this->allergen_ids)) {
            return collect();
        }

        return Allergen::whereIn('id', $this->allergen_ids)->get();
    }
}
