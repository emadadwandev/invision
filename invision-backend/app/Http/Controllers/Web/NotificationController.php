<?php

namespace App\Http\Controllers\Web;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Enums\TaskAssignmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\CreateTaskAssignmentRequest;
use App\Http\Requests\Notification\SendMessageRequest;
use App\Http\Requests\Notification\SendNotificationRequest;
use App\Models\Message;
use App\Models\Notification;
use App\Models\TaskAssignment;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    // ─── Notifications ──────────────────────────

    public function notifications(Request $request): View
    {
        $notifications = $this->notificationService->listNotifications(
            $request->only(['type', 'priority', 'is_read']),
        );
        return view('pages.notifications.index', compact('notifications'));
    }

    public function createNotification(): View
    {
        $users = User::select('id', 'name')->orderBy('name')->get();
        return view('pages.notifications.create', compact('users'));
    }

    public function storeNotification(SendNotificationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $userIds = $data['user_ids'];
        unset($data['user_ids']);

        $this->notificationService->sendBulkNotifications($userIds, $data);

        return redirect()->route('notifications.index')
            ->with('success', 'Notification sent successfully.');
    }

    public function destroyNotification(Notification $notification): RedirectResponse
    {
        $this->notificationService->deleteNotification($notification);
        return redirect()->route('notifications.index')
            ->with('success', 'Notification deleted.');
    }

    // ─── Messages ──────────────────────────────

    public function messages(Request $request): View
    {
        $messages = $this->notificationService->listMessages(
            $request->only(['search']),
        );
        return view('pages.notifications.messages', compact('messages'));
    }

    public function composeMessage(): View
    {
        $users = User::select('id', 'name')->orderBy('name')->get();
        return view('pages.notifications.compose', compact('users'));
    }

    public function sendMessage(SendMessageRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $recipientIds = $data['recipient_ids'];
        unset($data['recipient_ids']);
        $data['sender_id'] = $request->user()->id;

        $this->notificationService->sendMessage($data, $recipientIds);

        return redirect()->route('messages.index')
            ->with('success', 'Message sent successfully.');
    }

    public function showMessage(Message $message): View
    {
        $this->authorize('view', $message);
        $message = $this->notificationService->showMessage($message);
        return view('pages.notifications.message-show', compact('message'));
    }

    public function destroyMessage(Message $message): RedirectResponse
    {
        $this->authorize('delete', $message);
        $this->notificationService->deleteMessage($message);
        return redirect()->route('messages.index')
            ->with('success', 'Message deleted.');
    }

    // ─── Task Assignments ──────────────────────

    public function taskAssignments(Request $request): View
    {
        $tasks = $this->notificationService->listTaskAssignments(
            $request->only(['search', 'status', 'priority']),
        );
        $users = User::select('id', 'name')->orderBy('name')->get();
        return view('pages.notifications.tasks', compact('tasks', 'users'));
    }

    public function createTask(): View
    {
        $users = User::select('id', 'name')->orderBy('name')->get();
        return view('pages.notifications.task-create', compact('users'));
    }

    public function storeTask(CreateTaskAssignmentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['assigned_by'] = $request->user()->id;

        $this->notificationService->createTaskAssignment($data);

        return redirect()->route('task-assignments.index')
            ->with('success', 'Task assigned successfully.');
    }

    public function showTask(TaskAssignment $taskAssignment): View
    {
        $taskAssignment->load(['assigner', 'assignee']);
        return view('pages.notifications.task-show', compact('taskAssignment'));
    }

    public function verifyTask(TaskAssignment $taskAssignment): RedirectResponse
    {
        $this->notificationService->verifyTask($taskAssignment);
        return redirect()->route('task-assignments.show', $taskAssignment)
            ->with('success', 'Task verified.');
    }

    public function rejectTask(Request $request, TaskAssignment $taskAssignment): RedirectResponse
    {
        $this->notificationService->rejectTask($taskAssignment, $request->input('reason'));
        return redirect()->route('task-assignments.show', $taskAssignment)
            ->with('success', 'Task rejected.');
    }

    public function destroyTask(TaskAssignment $taskAssignment): RedirectResponse
    {
        $this->notificationService->deleteTaskAssignment($taskAssignment);
        return redirect()->route('task-assignments.index')
            ->with('success', 'Task deleted.');
    }
}
