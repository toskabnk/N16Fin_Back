<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Center extends Model
{
    protected $table = 'centers';

    protected $fillable = [
        'name',
        'acronym',
        'city',
    ];

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
