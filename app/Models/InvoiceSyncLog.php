<?php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class InvoiceSyncLog extends Model
{
    protected $collection = 'invoice_sync_logs';

    protected $fillable = [
        'synced_at',
        'added_invoice_ids',
        'removed_invoice_ids',
        'added_supplier_ids',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
        'added_invoice_ids' => 'array',
        'removed_invoice_ids' => 'array',
        'added_supplier_ids' => 'array',
    ];
}
