<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
class BusinessLine extends Model
{
    protected $table = 'business_lines';

    protected $fillable = [
        'name',
        'acronym',
        'description',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}