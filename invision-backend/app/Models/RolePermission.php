<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RolePermission extends Model
{
    protected $table = 'role_permission';

    protected $fillable = [
        'role',
        'permission_id',
    ];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
        ];
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }
}
