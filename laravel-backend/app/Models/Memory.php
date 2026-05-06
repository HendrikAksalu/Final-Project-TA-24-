<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Memory extends Model
{
    protected $fillable = [
        'album_id',
        'user_id',
        'title',
        'story',
        'who',
        'when',
        'where_note',
        'image_url',
        'image_thumb_url',
        'photo_class',
        'favorite',
        'rotate',
        'face_markers',
    ];

    protected function casts(): array
    {
        return [
            'favorite' => 'boolean',
            'face_markers' => 'array',
        ];
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
