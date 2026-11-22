<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredictiveSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'metric_type',
        'period_date',
        'projected_value',
        'actual_value',
        'confidence_score',
        'parameters',
        'limitations_note',
    ];

    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'projected_value' => 'decimal:2',
            'actual_value' => 'decimal:2',
            'confidence_score' => 'decimal:2',
            'parameters' => 'array',
        ];
    }
}
