<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GpsPositionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $tenantId,
        public readonly int $userId,
        public readonly string $userName,
        public readonly string $role,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly ?float $speedKmh,
        public readonly ?int $routeInstanceId,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->tenantId}.tracking"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'gps.position.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'role' => $this->role,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'speed_kmh' => $this->speedKmh,
            'route_instance_id' => $this->routeInstanceId,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
