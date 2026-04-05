<?php

namespace App\Models;

use App\Enums\NotificationPriority;
use App\Enums\TaskAssignmentStatus;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAssignment extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'assigned_by',
        'assigned_to',
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'proof_photo_path',
        'completion_notes',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'priority' => NotificationPriority::class,
            'status' => TaskAssignmentStatus::class,
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isPast()
            && !in_array($this->status, [TaskAssignmentStatus::Completed, TaskAssignmentStatus::Verified]);
    }
}
