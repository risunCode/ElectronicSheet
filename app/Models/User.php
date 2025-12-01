<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'referred_by',
        'theme',
        'current_session_id',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('assigned_at', 'assigned_by');
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'owner_id');
    }

    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class, 'owner_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'owner_id');
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function referralCodes(): HasMany
    {
        return $this->hasMany(ReferralCode::class, 'created_by');
    }

    // Helper methods
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isUser(): bool
    {
        return $this->hasRole('user');
    }

    public function isGuest(): bool
    {
        return $this->hasRole('guest');
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function canCreateDocuments(): bool
    {
        return $this->isAdmin() || $this->isUser();
    }

    public function canEditDocuments(): bool
    {
        return $this->isAdmin() || $this->isUser();
    }

    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo) {
            return asset('storage/' . $this->profile_photo);
        }
        
        // Return initials-based avatar using UI Avatars
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }
}
