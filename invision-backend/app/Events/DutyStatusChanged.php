<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DutyStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $tenantId,
        public readonly int $userId,
        public readonly string $userName,
        public readonly bool $isOnDuty,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->tenantId}.duty"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'duty.status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'is_on_duty' => $this->isOnDuty,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
