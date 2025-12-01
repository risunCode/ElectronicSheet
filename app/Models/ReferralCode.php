<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralCode extends Model
{
    protected $fillable = [
        'code',
        'created_by',
        'assigned_role_id',
        'max_uses',
        'current_uses',
        'expires_at',
        'is_active',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'max_uses' => 'integer',
            'current_uses' => 'integer',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'assigned_role_id');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(ReferralUsage::class);
    }

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->current_uses >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function incrementUsage(): void
    {
        $this->increment('current_uses');
    }
}
