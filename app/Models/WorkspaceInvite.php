<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WorkspaceInvite extends Model
{
    protected $fillable = [
        'workspace_id',
        'invited_by',
        'email',
        'token',
        'role',
        'expires_at',
        'accepted_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isValid(): bool
    {
        return $this->accepted_at === null
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public function accept(User $user): void
    {
        $this->workspace->users()->syncWithoutDetaching([
            $user->id => ['role' => $this->role],
        ]);

        $this->update(['accepted_at' => now()]);
    }
}
