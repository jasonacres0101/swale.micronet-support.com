<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'camera_id',
    'message_uid',
    'serial_number',
    'from_email',
    'subject',
    'attachment_path',
    'attachment_name',
    'attachment_mime',
    'attachment_size',
    'received_at',
    'imported_at',
])]
class CameraEmailSnapshot extends Model
{
    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'imported_at' => 'datetime',
        ];
    }

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    public function attachmentUrl(): ?string
    {
        if (! $this->attachment_path) {
            return null;
        }

        return Storage::disk('public')->url($this->attachment_path);
    }
}
