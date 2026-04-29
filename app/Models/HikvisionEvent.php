<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class HikvisionEvent extends Model
{
    protected $fillable = [
        'camera_id',
        'source_ip',
        'event_type',
        'event_state',
        'event_description',
        'event_time',
        'mac_address',
        'ip_address',
        'raw_payload',
        'parsed_payload',
    ];

    protected function casts(): array
    {
        return [
            'event_time' => 'datetime',
            'parsed_payload' => 'array',
        ];
    }

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }
}
