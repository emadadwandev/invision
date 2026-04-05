<?php

namespace App\Http\Requests\Notification;

use App\Enums\NotificationPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTaskAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['sometimes', Rule::enum(NotificationPriority::class)],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }
}
