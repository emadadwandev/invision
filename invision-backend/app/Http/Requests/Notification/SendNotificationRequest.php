<?php

namespace App\Http\Requests\Notification;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'exists:users,id'],
            'type' => ['sometimes', Rule::enum(NotificationType::class)],
            'priority' => ['sometimes', Rule::enum(NotificationPriority::class)],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'data' => ['nullable', 'array'],
        ];
    }
}
