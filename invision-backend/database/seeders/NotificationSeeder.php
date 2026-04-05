<?php

namespace Database\Seeders;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Enums\TaskAssignmentStatus;
use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\Notification;
use App\Models\TaskAssignment;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        if (! $tenant) {
            return;
        }

        $users = $tenant->users()->limit(5)->get();
        if ($users->isEmpty()) {
            return;
        }

        $admin = $users->first();
        $otherUsers = $users->skip(1);

        // System Notifications
        foreach ($users as $user) {
            Notification::firstOrCreate(
                ['tenant_id' => $tenant->id, 'user_id' => $user->id, 'title' => 'Welcome to Invision'],
                [
                    'type' => NotificationType::System->value,
                    'priority' => NotificationPriority::Normal->value,
                    'body' => 'Welcome to the Invision platform. Explore your dashboard to get started.',
                ],
            );
        }

        Notification::firstOrCreate(
            ['tenant_id' => $tenant->id, 'user_id' => $admin->id, 'title' => 'New Campaign Launched'],
            [
                'type' => NotificationType::Announcement->value,
                'priority' => NotificationPriority::High->value,
                'body' => 'A new promotional campaign has been launched. Check the campaigns section for details.',
            ],
        );

        Notification::firstOrCreate(
            ['tenant_id' => $tenant->id, 'user_id' => $admin->id, 'title' => 'Weekly Report Available'],
            [
                'type' => NotificationType::Alert->value,
                'priority' => NotificationPriority::Normal->value,
                'body' => 'Your weekly sales report is now available for review.',
                'read_at' => now(),
            ],
        );

        // Group Message
        $groupMsg = Message::firstOrCreate(
            ['tenant_id' => $tenant->id, 'subject' => 'Team Meeting Tomorrow'],
            [
                'sender_id' => $admin->id,
                'body' => 'Reminder: We have a team meeting tomorrow at 10 AM. Please prepare your weekly progress reports.',
                'is_group' => true,
            ],
        );

        foreach ($otherUsers as $user) {
            MessageRecipient::firstOrCreate(
                ['message_id' => $groupMsg->id, 'user_id' => $user->id],
                ['read_at' => rand(0, 1) ? now() : null],
            );
        }

        // Direct Message
        if ($otherUsers->isNotEmpty()) {
            $recipient = $otherUsers->first();
            $directMsg = Message::firstOrCreate(
                ['tenant_id' => $tenant->id, 'subject' => 'Route Schedule Update'],
                [
                    'sender_id' => $admin->id,
                    'body' => 'Your route schedule for next week has been updated. Please review the changes in the Routes section.',
                    'is_group' => false,
                ],
            );

            MessageRecipient::firstOrCreate(
                ['message_id' => $directMsg->id, 'user_id' => $recipient->id],
            );
        }

        // Task Assignments
        if ($otherUsers->count() >= 1) {
            TaskAssignment::firstOrCreate(
                ['tenant_id' => $tenant->id, 'title' => 'Complete Store Audit - Downtown'],
                [
                    'assigned_by' => $admin->id,
                    'assigned_to' => $otherUsers->values()[0]->id,
                    'description' => 'Perform a full audit of the Downtown store. Check product placements, inventory levels, and POSM materials.',
                    'priority' => NotificationPriority::High->value,
                    'status' => TaskAssignmentStatus::InProgress->value,
                    'due_date' => now()->addDays(3),
                ],
            );
        }

        if ($otherUsers->count() >= 2) {
            TaskAssignment::firstOrCreate(
                ['tenant_id' => $tenant->id, 'title' => 'Deliver POSM Materials'],
                [
                    'assigned_by' => $admin->id,
                    'assigned_to' => $otherUsers->values()[1]->id,
                    'description' => 'Deliver promotional banners and shelf talkers to stores on Route A.',
                    'priority' => NotificationPriority::Normal->value,
                    'status' => TaskAssignmentStatus::Pending->value,
                    'due_date' => now()->addDays(5),
                ],
            );
        }

        if ($otherUsers->count() >= 1) {
            TaskAssignment::firstOrCreate(
                ['tenant_id' => $tenant->id, 'title' => 'Submit Weekly Sales Report'],
                [
                    'assigned_by' => $admin->id,
                    'assigned_to' => $otherUsers->values()[0]->id,
                    'description' => 'Compile and submit the weekly sales report for your assigned territory.',
                    'priority' => NotificationPriority::Urgent->value,
                    'status' => TaskAssignmentStatus::Completed->value,
                    'due_date' => now()->subDay(),
                    'completion_notes' => 'Report submitted and uploaded to the shared drive.',
                    'completed_at' => now()->subHours(6),
                ],
            );
        }
    }
}
