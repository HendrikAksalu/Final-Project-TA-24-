<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Album extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'photo_class',
        'rotate',
        'cover_thumb_url',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'album_collaborators')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function memories(): HasMany
    {
        return $this->hasMany(Memory::class);
    }

    public function userCanAccess(User $user): bool
    {
        if ($this->user_id === $user->id) {
            return true;
        }

        return $this->collaborators()->where('users.id', $user->id)->exists();
    }

    public function userCanEdit(User $user): bool
    {
        if ($this->user_id === $user->id) {
            return true;
        }

        return $this->collaborators()
            ->where('users.id', $user->id)
            ->wherePivot('role', 'editor')
            ->exists();
    }

    public function userRole(User $user): ?string
    {
        if ($this->user_id === $user->id) {
            return 'owner';
        }

        $role = $this->collaborators()
            ->where('users.id', $user->id)
            ->first()?->pivot->role;

        return $role;
    }

    public function syncCoverFromThumb(?string $thumbUrl): void
    {
        if (! $thumbUrl) {
            return;
        }

        $this->cover_thumb_url = $thumbUrl;
        $this->saveQuietly();
    }
}
