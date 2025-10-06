<?php

namespace App\Models\Contact;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactList extends Model
{
    protected $table = 'contact_lists';

    protected $fillable = [
        'name',
        'description',
        'color',
        'user_id'
    ];

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contacts_in_lists', 'contact_list_id', 'contact_id');
    }
}
