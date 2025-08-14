<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Concept extends Model
{
    protected $table = 'concepts';

    protected $fillable = [
        'name',
    ];

}