<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'odoo_supplier_id',
        'type',
        'center_id',
    ];

    public function invoices() : HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function center() : BelongsTo
    {
        return $this->belongsTo(Center::class);
    }
}
