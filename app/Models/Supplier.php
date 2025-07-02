<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use MongoDB\Laravel\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'odoo_supplier_id',
        'type',
        'centers',
    ];

    protected $casts = [
        'centers' => 'array',
    ];

    public function invoices() : HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
