<?php

namespace App\Models;

use App\Enums\TeamPosition;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'parent_team_id',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function parentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'parent_team_id');
    }

    public function childTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'parent_team_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_members')
            ->withPivot('position', 'joined_at')
            ->withTimestamps();
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function leader(): BelongsToMany
    {
        return $this->members()->wherePivot('position', TeamPosition::Leader->value);
    }

    public function transfersFrom(): HasMany
    {
        return $this->hasMany(TeamTransfer::class, 'from_team_id');
    }

    public function transfersTo(): HasMany
    {
        return $this->hasMany(TeamTransfer::class, 'to_team_id');
    }
}
