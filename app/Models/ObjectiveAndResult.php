<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class ObjectiveAndResult extends Model
{
    protected $table = 'objectives_and_results';
    protected $fillable = [
        'id_business_line',
        'id_center',
        'factures_year_before',
        'results_year_before',
        'results',
        'projected_growth',
        'factures_1',
        'factures_2',
        'year',
    ];

    protected $casts = [
        'factures_year_before' => 'array',
        'results_year_before' => 'array',
        'results' => 'array',
        'factures_1' => 'array',
        'factures_2' => 'array'
    ];

    public function businessLine() : BelongsTo
    {
        return $this->belongsTo(BusinessLine::class, 'id_business_line');
    }

    public function center() : BelongsTo
    {
        return $this->belongsTo(Center::class, 'id_center');
    }
}
