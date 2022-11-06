<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonOfInterest extends Model
{
    use HasFactory;

    protected $table = 'people_of_interest';

    protected $fillable = [
        'name',
        'tag',
    ];
}
