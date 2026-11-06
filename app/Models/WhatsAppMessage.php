<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessage extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'jamaah_id',
        'phone_number',
        'message_type',
        'content',
        'status',
        'response_payload',
        'error_message',
        'sent_at',
        'sent_by',
    ];

    protected function casts(): array
    {
        return [
            'response_payload' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function jamaah(): BelongsTo
    {
        return $this->belongsTo(Jamaah::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
