<?php

namespace App\Models\PriceOffer;

use App\Models\Project\Project;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class PriceOffer extends Model
{
    protected $fillable = [
        'code',
        'user_id',
        'project_id',
        'json',
        'total_hours',
        'total_price',
        'total_price_vat',
        'valid_to'
    ];

    protected $casts = [
        'json' => 'json',
        'valid_to' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function getTotalPriceAttribute($value)
    {
        return number_format($value, 2, '.', '');
    }

    public function getTotalPriceVatAttribute($value)
    {
        return number_format($value, 2, '.', '');
    }

    public function getTotalHoursAttribute($value)
    {
        return number_format($value, 2, '.', '');
    }

    public function getValidToAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function generateCode()
    {
        $lastPriceOffer = self::orderBy('id', 'desc')->first();
        $lastCode = $lastPriceOffer ? $lastPriceOffer->code : null;

        if ($lastCode) {
            $lastNumber = (int) substr($lastCode, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            return 'PO-' . date('Y') . '-' . $newNumber;
        }

        return 'PO-' . date('Y') . '-0001';
    }
}
