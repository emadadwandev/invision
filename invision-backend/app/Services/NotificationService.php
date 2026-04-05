<?php

namespace App\Services;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Enums\TaskAssignmentStatus;
use App\Events\NotificationPushed;
use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\Notification;
use App\Models\TaskAssignment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    // ─── Notifications ──────────────────────────────────────────

    public function listNotifications(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Notification::with('user');

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['is_read'])) {
            if ($filters['is_read']) {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function sendNotification(array $data): Notification
    {
        $notification = Notification::create([
            'tenant_id' => $data['tenant_id'] ?? app('current_tenant_id'),
            'user_id' => $data['user_id'],
            'type' => $data['type'] ?? NotificationType::System->value,
            'priority' => $data['priority'] ?? NotificationPriority::Normal->value,
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'data' => $data['data'] ?? null,
        ]);

        // Broadcast real-time notification
        NotificationPushed::dispatch(
            $data['user_id'],
            $data['type'] ?? NotificationType::System->value,
            $data['title'],
            $data['body'] ?? '',
            $data['action_url'] ?? null,
            $data['data'] ?? null,
        );

        return $notification;
    }

    public function sendBulkNotifications(array $userIds, array $data): int
    {
        $count = 0;
        foreach ($userIds as $userId) {
            $this->sendNotification(array_merge($data, ['user_id' => $userId]));
            $count++;
        }
        return $count;
    }

    public function markAsRead(Notification $notification): Notification
    {
        $notification->update(['read_at' => now()]);
        return $notification->fresh();
    }

    public function markAllRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function deleteNotification(Notification $notification): void
    {
        $notification->delete();
    }

    public function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    // ─── Messages ──────────────────────────────────────────

    public function listMessages(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Message::with(['sender', 'recipients.user']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['sender_id'])) {
            $query->where('sender_id', $filters['sender_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function getInbox(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Message::with(['sender', 'recipients'])
            ->whereHas('recipients', function ($q) use ($userId, $filters) {
                $q->where('user_id', $userId);
                if (isset($filters['archived']) && $filters['archived']) {
                    $q->whereNotNull('archived_at');
                } else {
                    $q->whereNull('archived_at');
                }
            });

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function sendMessage(array $data, array $recipientIds): Message
    {
        return DB::transaction(function () use ($data, $recipientIds) {
            $message = Message::create([
                'tenant_id' => $data['tenant_id'] ?? app('current_tenant_id'),
                'sender_id' => $data['sender_id'],
                'subject' => $data['subject'],
                'body' => $data['body'],
                'is_group' => count($recipientIds) > 1,
            ]);

            foreach ($recipientIds as $recipientId) {
                MessageRecipient::create([
                    'message_id' => $message->id,
                    'user_id' => $recipientId,
                ]);
            }

            return $message->load(['sender', 'recipients.user']);
        });
    }

    public function showMessage(Message $message): Message
    {
        return $message->load(['sender', 'recipients.user']);
    }

    public function markMessageRead(int $messageId, int $userId): void
    {
        MessageRecipient::where('message_id', $messageId)
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function archiveMessage(int $messageId, int $userId): void
    {
        MessageRecipient::where('message_id', $messageId)
            ->where('user_id', $userId)
            ->update(['archived_at' => now()]);
    }

    public function deleteMessage(Message $message): void
    {
        $message->delete();
    }

    // ─── Task Assignments ──────────────────────────────────

    public function listTaskAssignments(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = TaskAssignment::with(['assigner', 'assignee']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (!empty($filters['assigned_by'])) {
            $query->where('assigned_by', $filters['assigned_by']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function createTaskAssignment(array $data): TaskAssignment
    {
        $task = TaskAssignment::create([
            'tenant_id' => $data['tenant_id'] ?? app('current_tenant_id'),
            'assigned_by' => $data['assigned_by'],
            'assigned_to' => $data['assigned_to'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'] ?? NotificationPriority::Normal->value,
            'due_date' => $data['due_date'] ?? null,
        ]);

        // Send notification to assignee
        $this->sendNotification([
            'user_id' => $data['assigned_to'],
            'type' => NotificationType::Task->value,
            'priority' => $data['priority'] ?? NotificationPriority::Normal->value,
            'title' => 'New Task Assigned',
            'body' => "You have been assigned: {$data['title']}",
            'data' => ['task_assignment_id' => $task->id],
        ]);

        return $task->load(['assigner', 'assignee']);
    }

    public function updateTaskAssignment(TaskAssignment $task, array $data): TaskAssignment
    {
        $task->update($data);
        return $task->fresh(['assigner', 'assignee']);
    }

    public function completeTask(TaskAssignment $task, ?string $proofPhotoPath = null, ?string $notes = null): TaskAssignment
    {
        $task->update([
            'status' => TaskAssignmentStatus::Completed->value,
            'proof_photo_path' => $proofPhotoPath,
            'completion_notes' => $notes,
            'completed_at' => now(),
        ]);

        // Notify the assigner
        $this->sendNotification([
            'user_id' => $task->assigned_by,
            'type' => NotificationType::Task->value,
            'title' => 'Task Completed',
            'body' => "Task \"{$task->title}\" has been completed by {$task->assignee->name}.",
            'data' => ['task_assignment_id' => $task->id],
        ]);

        return $task->fresh(['assigner', 'assignee']);
    }

    public function verifyTask(TaskAssignment $task): TaskAssignment
    {
        $task->update(['status' => TaskAssignmentStatus::Verified->value]);

        $this->sendNotification([
            'user_id' => $task->assigned_to,
            'type' => NotificationType::Task->value,
            'title' => 'Task Verified',
            'body' => "Your task \"{$task->title}\" has been verified.",
            'data' => ['task_assignment_id' => $task->id],
        ]);

        return $task->fresh(['assigner', 'assignee']);
    }

    public function rejectTask(TaskAssignment $task, ?string $reason = null): TaskAssignment
    {
        $task->update([
            'status' => TaskAssignmentStatus::Rejected->value,
            'completion_notes' => $reason,
        ]);

        $this->sendNotification([
            'user_id' => $task->assigned_to,
            'type' => NotificationType::Task->value,
            'priority' => NotificationPriority::High->value,
            'title' => 'Task Rejected',
            'body' => "Your task \"{$task->title}\" has been rejected." . ($reason ? " Reason: {$reason}" : ''),
            'data' => ['task_assignment_id' => $task->id],
        ]);

        return $task->fresh(['assigner', 'assignee']);
    }

    public function deleteTaskAssignment(TaskAssignment $task): void
    {
        $task->delete();
    }

    public function getMyTasks(int $userId, ?string $status = null): LengthAwarePaginator
    {
        $query = TaskAssignment::with(['assigner'])
            ->where('assigned_to', $userId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->paginate(15);
    }
}
