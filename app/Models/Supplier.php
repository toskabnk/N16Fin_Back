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
        'centers',
        'only_add_vat',
        'business_line_id',
        'share_type_id',
        'concept'
    ];

    protected $casts = [
        'centers' => 'array',
        'only_add_vat' => 'boolean',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function businessLine(): BelongsTo
    {
        return $this->belongsTo(BusinessLine::class);
    }

    public function shareType(): BelongsTo
    {
        return $this->belongsTo(ShareType::class);
    }
}
