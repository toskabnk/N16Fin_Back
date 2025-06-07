<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'odoo_supplier_id',
        'type',
        'center_id',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function center()
    {
        return $this->belongsTo(Center::class);
    }
}
