<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'brand_tagline',
        'email',
        'phone',
        'whatsapp',
        'address',
        'website',
        'registration_prefix',
        'invoice_prefix',
        'receipt_prefix',
        'receipt_footer_note',
    ];
}
