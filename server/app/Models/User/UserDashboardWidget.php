<?php

namespace App\Models\User;

use App\Models\Site\Site;
use Illuminate\Database\Eloquent\Model;

class UserDashboardWidget extends Model
{
    protected $table = 'user_dashboard_widgets';

    protected $fillable = [
        'user_id',
        'site_id',
        'widget_key',
        'position',
        'size',
        'enabled',
    ];

    protected $casts = [
        'position' => 'integer',
        'enabled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id', 'id');
    }
}
