<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VisitStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $tenantId,
        public readonly int $routeInstanceId,
        public readonly int $storeVisitId,
        public readonly int $userId,
        public readonly string $status,
        public readonly ?int $storeId = null,
        public readonly ?string $storeName = null,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->tenantId}.visits"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'visit.status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'route_instance_id' => $this->routeInstanceId,
            'store_visit_id' => $this->storeVisitId,
            'user_id' => $this->userId,
            'status' => $this->status,
            'store_id' => $this->storeId,
            'store_name' => $this->storeName,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
