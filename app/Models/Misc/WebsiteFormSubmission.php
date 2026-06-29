<?php

namespace App\Models\Misc;

use Illuminate\Database\Eloquent\Model;

class WebsiteFormSubmission extends Model
{
    protected $fillable = ['uuid', 'form_key', 'status', 'step', 'email', 'full_name', 'phone', 'suburb', 'postcode', 'state', 'rejection_reason', 'zoho_lead_id', 'zoho_status', 'payload',
        'zoho_response', 'ip_address', 'user_agent',
    ];

    protected $casts = ['payload' => 'array', 'zoho_response' => 'array',];
}