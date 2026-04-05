<?php

namespace App\Http\Requests\Route;

use App\Enums\RouteStatus;
use App\Enums\VisitFrequency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoutePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'assigned_to' => ['sometimes', 'exists:users,id'],
            'frequency' => ['sometimes', Rule::enum(VisitFrequency::class)],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['sometimes', Rule::enum(RouteStatus::class)],
            'stores' => ['nullable', 'array'],
            'stores.*.store_id' => ['required_with:stores', 'exists:stores,id'],
            'stores.*.visit_order' => ['nullable', 'integer', 'min:1'],
            'stores.*.expected_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:480'],
            'stores.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
