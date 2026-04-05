<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\CreateTaskAssignmentRequest;
use App\Http\Requests\Notification\SendMessageRequest;
use App\Http\Requests\Notification\SendNotificationRequest;
use App\Http\Requests\Notification\UpdateTaskAssignmentRequest;
use App\Http\Resources\MessageResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\TaskAssignmentResource;
use App\Models\Message;
use App\Models\Notification;
use App\Models\TaskAssignment;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    // ─── Notifications ──────────────────────────

    public function notifications(Request $request): AnonymousResourceCollection
    {
        $notifications = $this->notificationService->listNotifications(
            $request->only(['type', 'priority', 'is_read', 'user_id']),
        );
        return NotificationResource::collection($notifications);
    }

    public function sendNotification(SendNotificationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $userIds = $data['user_ids'];
        unset($data['user_ids']);

        $count = $this->notificationService->sendBulkNotifications($userIds, $data);

        return response()->json([
            'message' => "Notification sent to {$count} user(s).",
        ], 201);
    }

    public function markRead(Notification $notification): NotificationResource
    {
        $notification = $this->notificationService->markAsRead($notification);
        return new NotificationResource($notification);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $count = $this->notificationService->markAllRead($request->user()->id);
        return response()->json(['message' => "{$count} notification(s) marked as read."]);
    }

    public function destroyNotification(Notification $notification): JsonResponse
    {
        $this->notificationService->deleteNotification($notification);
        return response()->json(['message' => 'Notification deleted.']);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount($request->user()->id);
        return response()->json(['unread_count' => $count]);
    }

    // ─── My Notifications (Mobile) ─────────────

    public function myNotifications(Request $request): AnonymousResourceCollection
    {
        $notifications = $this->notificationService->listNotifications(
            array_merge($request->only(['type', 'priority', 'is_read']), [
                'user_id' => $request->user()->id,
            ]),
        );
        return NotificationResource::collection($notifications);
    }

    // ─── Messages ──────────────────────────────

    public function messages(Request $request): AnonymousResourceCollection
    {
        $messages = $this->notificationService->listMessages(
            $request->only(['search', 'sender_id']),
        );
        return MessageResource::collection($messages);
    }

    public function inbox(Request $request): AnonymousResourceCollection
    {
        $messages = $this->notificationService->getInbox(
            $request->user()->id,
            $request->only(['search', 'archived']),
        );
        return MessageResource::collection($messages);
    }

    public function sendMessage(SendMessageRequest $request): JsonResponse
    {
        $data = $request->validated();
        $recipientIds = $data['recipient_ids'];
        unset($data['recipient_ids']);
        $data['sender_id'] = $request->user()->id;

        $message = $this->notificationService->sendMessage($data, $recipientIds);

        return (new MessageResource($message))
            ->response()
            ->setStatusCode(201);
    }

    public function showMessage(Message $message): MessageResource
    {
        $this->authorize('view', $message);
        $message = $this->notificationService->showMessage($message);
        return new MessageResource($message);
    }

    public function markMessageRead(Message $message, Request $request): JsonResponse
    {
        $this->notificationService->markMessageRead($message->id, $request->user()->id);
        return response()->json(['message' => 'Message marked as read.']);
    }

    public function archiveMessage(Message $message, Request $request): JsonResponse
    {
        $this->notificationService->archiveMessage($message->id, $request->user()->id);
        return response()->json(['message' => 'Message archived.']);
    }

    public function destroyMessage(Message $message): JsonResponse
    {
        $this->authorize('delete', $message);
        $this->notificationService->deleteMessage($message);
        return response()->json(['message' => 'Message deleted.']);
    }

    // ─── Task Assignments ──────────────────────

    public function tasks(Request $request): AnonymousResourceCollection
    {
        $tasks = $this->notificationService->listTaskAssignments(
            $request->only(['search', 'status', 'priority', 'assigned_to', 'assigned_by']),
        );
        return TaskAssignmentResource::collection($tasks);
    }

    public function storeTask(CreateTaskAssignmentRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['assigned_by'] = $request->user()->id;

        $task = $this->notificationService->createTaskAssignment($data);

        return (new TaskAssignmentResource($task))
            ->response()
            ->setStatusCode(201);
    }

    public function showTask(TaskAssignment $taskAssignment): TaskAssignmentResource
    {
        $taskAssignment->load(['assigner', 'assignee']);
        return new TaskAssignmentResource($taskAssignment);
    }

    public function updateTask(UpdateTaskAssignmentRequest $request, TaskAssignment $taskAssignment): TaskAssignmentResource
    {
        $task = $this->notificationService->updateTaskAssignment($taskAssignment, $request->validated());
        return new TaskAssignmentResource($task);
    }

    public function completeTask(Request $request, TaskAssignment $taskAssignment): TaskAssignmentResource
    {
        $task = $this->notificationService->completeTask(
            $taskAssignment,
            $request->input('proof_photo_path'),
            $request->input('completion_notes'),
        );
        return new TaskAssignmentResource($task);
    }

    public function verifyTask(TaskAssignment $taskAssignment): TaskAssignmentResource
    {
        $task = $this->notificationService->verifyTask($taskAssignment);
        return new TaskAssignmentResource($task);
    }

    public function rejectTask(Request $request, TaskAssignment $taskAssignment): TaskAssignmentResource
    {
        $task = $this->notificationService->rejectTask($taskAssignment, $request->input('reason'));
        return new TaskAssignmentResource($task);
    }

    public function destroyTask(TaskAssignment $taskAssignment): JsonResponse
    {
        $this->notificationService->deleteTaskAssignment($taskAssignment);
        return response()->json(['message' => 'Task assignment deleted.']);
    }

    // ─── My Tasks (Mobile) ─────────────────────

    public function myAssignedTasks(Request $request): AnonymousResourceCollection
    {
        $tasks = $this->notificationService->getMyTasks(
            $request->user()->id,
            $request->input('status'),
        );
        return TaskAssignmentResource::collection($tasks);
    }
}
