<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'body' => $this->body,
            'is_group' => $this->is_group,
            'sender' => [
                'id' => $this->sender?->id,
                'name' => $this->sender?->name,
            ],
            'recipients' => $this->whenLoaded('recipients', function () {
                return $this->recipients->map(function ($r) {
                    return [
                        'id' => $r->user?->id,
                        'name' => $r->user?->name,
                        'read_at' => $r->read_at,
                        'archived_at' => $r->archived_at,
                    ];
                });
            }),
            'created_at' => $this->created_at,
        ];
    }
}
