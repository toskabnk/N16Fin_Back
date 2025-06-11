<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\HasMany;

class BusinessLine extends Model
{
    protected $table = 'business_lines';

    protected $fillable = [
        'name',
        'acronym',
        'description',
    ];

    public function invoices() : HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}