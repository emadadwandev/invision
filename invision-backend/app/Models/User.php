<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasTenant, Notifiable, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'role',
        'locale',
        'avatar_path',
        'mfa_secret',
        'mfa_enabled',
        'mfa_recovery_codes',
        'mfa_confirmed_at',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
    ];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'email_verified_at' => 'datetime',
            'mfa_confirmed_at' => 'datetime',
            'mfa_enabled' => 'boolean',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_members')
            ->withPivot('position', 'joined_at')
            ->withTimestamps();
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function teamTransfers(): HasMany
    {
        return $this->hasMany(TeamTransfer::class);
    }

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(UserRole ...$roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function isBackendUser(): bool
    {
        return $this->role->isBackendUser();
    }

    public function isMobileUser(): bool
    {
        return $this->role->isMobileUser();
    }
}
