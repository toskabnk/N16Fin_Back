<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Year extends Model
{
    protected $table = 'years';
    protected $fillable = [
        'year',
    ];
}
