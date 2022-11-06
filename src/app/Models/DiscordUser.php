<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscordUser extends Model
{
    use HasFactory;

    protected $table = 'discord_users';

    protected $fillable = [
        'discord_id',
        'role',
        'note',
        'person_of_interest_id'
    ];

    public function personOfInterest()
    {
        return $this->belongsTo(PersonOfInterest::class);
    }
}
