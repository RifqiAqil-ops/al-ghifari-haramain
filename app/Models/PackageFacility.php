<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageFacility extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'name',
        'is_included',
    ];

    protected function casts(): array
    {
        return [
            'is_included' => 'boolean',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
