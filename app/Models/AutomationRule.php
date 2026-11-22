<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'category',
        'trigger_type',
        'conditions',
        'action_type',
        'action_payload',
        'is_active',
        'is_autonomous',
        'last_run_at',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'action_payload' => 'array',
            'is_active' => 'boolean',
            'is_autonomous' => 'boolean',
            'last_run_at' => 'datetime',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AutomationLog::class, 'rule_id')->latest();
    }
}
