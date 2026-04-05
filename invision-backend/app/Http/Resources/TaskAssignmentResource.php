<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => [
                'value' => $this->priority->value,
                'label' => $this->priority->label(),
                'color' => $this->priority->color(),
            ],
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ],
            'assigner' => [
                'id' => $this->assigner?->id,
                'name' => $this->assigner?->name,
            ],
            'assignee' => [
                'id' => $this->assignee?->id,
                'name' => $this->assignee?->name,
            ],
            'due_date' => $this->due_date?->toDateString(),
            'is_overdue' => $this->isOverdue(),
            'proof_photo_path' => $this->proof_photo_path,
            'completion_notes' => $this->completion_notes,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
