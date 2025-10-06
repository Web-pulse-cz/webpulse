<?php

namespace App\Models\Contact;

use App\Models\Project\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Contact extends Model
{
    use HasFactory;

    protected $table = 'contacts';

    protected $fillable = [
        'firstname',
        'lastname',
        'phone_prefix',
        'phone',
        'email',
        'company',
        'street',
        'city',
        'zip',
        'occupation',
        'goal',
        'note',
        'user_id',
        'contact_id',
        'contact_source_id',
        'contact_phase_id',
        'next_meeting',
        'next_contact',
        'last_contacted_at'
    ];

    protected $casts = [
        'next_meeting' => 'datetime',
        'last_contacted_at' => 'datetime',
        'next_contact' => 'datetime'
    ];

    protected $with = ['phase', 'source'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'id');
    }

    public function phase()
    {
        return $this->belongsTo(ContactPhase::class, 'contact_phase_id', 'id');
    }

    public function source()
    {
        return $this->belongsTo(ContactSource::class, 'contact_source_id', 'id');
    }

    public function histories()
    {
        return $this->hasMany(ContactHistory::class, 'contact_id', 'id');
    }

    public function tasks()
    {
        return $this->belongsToMany(ContactTask::class, 'contacts_has_tasks', 'contact_id', 'contact_task_id');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'contact_id', 'id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'client_id', 'id');
    }

    public function lists()
    {
        return $this->belongsToMany(ContactList::class, 'contacts_in_lists', 'contact_id', 'contact_list_id');
    }

    public function syncTasks(Request $request)
    {
        $tasks = $request->get('tasks', []);
        DB::table('contacts_has_tasks')->where('contact_id', $this->id)->delete();
        if (!empty($tasks)) {
            foreach ($tasks as $task) {
                DB::table('contacts_has_tasks')->insert([
                    'contact_id' => $this->id,
                    'contact_task_id' => $task
                ]);
            }
        }
    }

    public function syncLists(Request $request)
    {
        $lists = $request->get('lists', []);
        DB::table('contacts_in_lists')->where('contact_id', $this->id)->delete();
        if (!empty($lists)) {
            foreach ($lists as $list) {
                DB::table('contacts_in_lists')->insert([
                    'contact_id' => $this->id,
                    'contact_list_id' => $list
                ]);
            }
        }
    }
}
