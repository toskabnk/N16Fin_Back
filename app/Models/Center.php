<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\HasMany;

class Center extends Model
{
    protected $table = 'centers';

    protected $fillable = [
        'name',
        'acronym',
        'city',
    ];

    public function suppliers() : HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function invoices() : HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
