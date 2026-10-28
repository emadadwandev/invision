<?php

namespace App\Http\Requests\Route;

use Illuminate\Foundation\Http\FormRequest;

class GpsLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Devices report speed/heading as -1 when unknown; drop those instead of
     * rejecting the whole batch (which made the app retry it forever).
     */
    protected function prepareForValidation(): void
    {
        $clean = function (array $log): array {
            foreach (['speed_kmh' => [0, INF], 'bearing' => [0, 360], 'accuracy_meters' => [0, INF]] as $key => [$min, $max]) {
                if (isset($log[$key]) && is_numeric($log[$key]) && ($log[$key] < $min || $log[$key] > $max)) {
                    $log[$key] = null;
                }
            }

            return $log;
        };

        $this->merge($clean($this->all()));

        if (is_array($this->input('logs'))) {
            $this->merge(['logs' => array_map(fn ($l) => is_array($l) ? $clean($l) : $l, $this->input('logs'))]);
        }
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
            'logs.*.route_instance_id' => ['nullable', 'exists:route_instances,id'],
        ];
    }
}
