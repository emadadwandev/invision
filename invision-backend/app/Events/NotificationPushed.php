<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationPushed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly string $type,
        public readonly string $title,
        public readonly string $body,
        public readonly ?string $actionUrl = null,
        public readonly ?array $data = null,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->userId}.notifications"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.pushed';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'action_url' => $this->actionUrl,
            'data' => $this->data,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
