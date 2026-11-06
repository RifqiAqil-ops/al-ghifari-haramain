<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jamaah extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jamaahs';

    protected $fillable = [
        'registration_number',
        'name',
        'nik',
        'family_card_number',
        'passport_number',
        'passport_expiry_date',
        'passport_issued_place',
        'birth_place',
        'birth_date',
        'gender',
        'address',
        'city',
        'province',
        'postal_code',
        'phone',
        'email',
        'emergency_contact_name',
        'emergency_contact_relation',
        'emergency_contact_phone',
        'photo',
        'special_needs',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'passport_expiry_date' => 'date',
            'birth_date' => 'date',
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(JamaahDocument::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(JamaahContact::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function whatsappMessages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }

    public function getGenderLabelAttribute(): string
    {
        return $this->gender === 'L' ? 'Laki-Laki' : 'Perempuan';
    }
}
