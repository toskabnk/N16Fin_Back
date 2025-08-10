<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class CenterCost extends Model
{
    protected $table = 'center_costs';

    protected $fillable = [
        'center_id',
        'year',
        'conceptos',
    ];


    // Normaliza SI llega como string; si llega array, úsalo tal cual
    public function setConceptosAttribute($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $this->attributes['conceptos'] = is_array($decoded) ? $decoded : [];
        } else {
            $this->attributes['conceptos'] = $value;
        }
    }

    public function getConceptosAttribute($value)
    {
        return is_string($value) ? (json_decode($value, true) ?: []) : $value;
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class, 'center_id', '_id');
    }
}
