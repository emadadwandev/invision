<?php

namespace App\Http\Requests\Notification;

use App\Enums\NotificationPriority;
use App\Enums\TaskAssignmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['sometimes', Rule::enum(NotificationPriority::class)],
            'status' => ['sometimes', Rule::enum(TaskAssignmentStatus::class)],
            'due_date' => ['nullable', 'date'],
            'proof_photo_path' => ['nullable', 'string', 'max:500'],
            'completion_notes' => ['nullable', 'string'],
        ];
    }
}
