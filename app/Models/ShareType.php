<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ShareType extends Model
{
    protected $table = 'share_types';

    protected $fillable = [
        'name',
        'configuration',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
