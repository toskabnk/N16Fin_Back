<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class CenterSalary extends Model
{
    protected $table = 'center_salaries'; // o 'salarios_centros'

    protected $fillable = [
        'center_id',    // referencia al ID de Center
        'año',
        'conceptos',
    ];

    protected $casts = [
        'conceptos' => 'array',
    ];

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class, 'center_id', '_id');
    }
}