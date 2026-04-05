<?php

namespace App\Http\Requests\Route;

use Illuminate\Foundation\Http\FormRequest;

class GpsLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required_without:logs', 'numeric', 'between:-90,90'],
            'longitude' => ['required_without:logs', 'numeric', 'between:-180,180'],
            'accuracy_meters' => ['nullable', 'numeric', 'min:0'],
            'speed_kmh' => ['nullable', 'numeric', 'min:0'],
            'bearing' => ['nullable', 'numeric', 'between:0,360'],
            'route_instance_id' => ['nullable', 'exists:route_instances,id'],
            'recorded_at' => ['nullable', 'date'],
            // Batch
            'logs' => ['nullable', 'array'],
            'logs.*.latitude' => ['required_with:logs', 'numeric', 'between:-90,90'],
            'logs.*.longitude' => ['required_with:logs', 'numeric', 'between:-180,180'],
            'logs.*.accuracy_meters' => ['nullable', 'numeric', 'min:0'],
            'logs.*.speed_kmh' => ['nullable', 'numeric', 'min:0'],
            'logs.*.bearing' => ['nullable', 'numeric', 'between:0,360'],
            'logs.*.recorded_at' => ['required_with:logs', 'date'],
        ];
    }
}
