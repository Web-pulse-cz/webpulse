<?php

namespace App\Models\Career;

use App\Traits\Siteable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CareerApplication extends Model
{
    use Siteable;

    protected $table = 'career_applications';

    protected $fillable = [
        'career_id',
        'firstname',
        'lastname',
        'email',
        'phone',
        'cover_letter',
        'resume',
        'status',
        'salary_expectation',
        'availability',
        'source',
        'locale',
        'user_id',
    ];

    public function career()
    {
        return $this->belongsTo(Career::class, 'career_id', 'id');
    }

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }

    public function getRealAvailabilityAttribute(): string
    {
        switch ($this->availability) {
            case 'immediate':
                return 'Ihned';
                break;
            case '1-week':
                return 'Do týdne';
                break;
            case '2-weeks':
                return 'Do dvou týdnů';
                break;
            case '1-month':
                return 'Do měsíce';
                break;
            case '2-months':
                return 'Do dvou měsíců';
                break;
            case 'negotiable':
                return 'Dohodou';
                break;
            default:
                return 'Neznámá dostupnost';
                break;
        }
    }
}
