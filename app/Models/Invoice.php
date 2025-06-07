<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $table = 'invoices';

    protected $fillable = [
        'odoo_invoice_id',
        'reference',
        'invoice_date',
        'month',
        'state',
        'manual',
        'concept',
        'amount_total',
        'status',
        'centers',
        'supplier_id',
        'share_type_id',
        'business_line_id',
        'type',
    ];

    protected $casts = [
        'invoice_date' => 'datetime',
        'centers' => 'array',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function businessLine()
    {
        return $this->belongsTo(BusinessLine::class);
    }
    public function shareType()
    {
        return $this->belongsTo(ShareType::class);
    }

}
