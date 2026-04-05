<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => [
                'value' => $this->type->value,
                'label' => $this->type->label(),
                'color' => $this->type->color(),
            ],
            'priority' => [
                'value' => $this->priority->value,
                'label' => $this->priority->label(),
                'color' => $this->priority->color(),
            ],
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
            'is_read' => $this->isRead(),
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
        ];
    }
}
