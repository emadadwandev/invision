<?php

namespace App\Http\Requests\Route;

use App\Enums\RouteStatus;
use App\Enums\VisitFrequency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRoutePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'assigned_to' => ['required', 'exists:users,id'],
            'frequency' => ['required', Rule::enum(VisitFrequency::class)],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', Rule::enum(RouteStatus::class)],
            'stores' => ['nullable', 'array'],
            'stores.*.store_id' => ['required_with:stores', 'exists:stores,id'],
            'stores.*.visit_order' => ['nullable', 'integer', 'min:1'],
            'stores.*.expected_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:480'],
            'stores.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
