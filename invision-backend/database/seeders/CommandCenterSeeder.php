<?php

namespace Database\Seeders;

use App\Models\GpsTrackingLog;
use App\Models\RouteInstance;
use App\Models\Tenant;
use App\Models\User;
use App\Enums\UserRole;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CommandCenterSeeder extends Seeder
{
    /**
     * Seed GPS tracking logs to make the Command Center dashboard useful.
     */
    public function run(): void
    {
        $tenant = Tenant::first();
        if (! $tenant) {
            return;
        }

        $fieldUsers = User::whereIn('role', [
            UserRole::FieldForce->value,
            UserRole::SalesRepresentative->value,
            UserRole::Promoter->value,
            UserRole::Merchandiser->value,
        ])->get();

        if ($fieldUsers->isEmpty()) {
            return;
        }

        $activeInstance = RouteInstance::whereDate('started_at', today())->first();

        // Simulate a GPS trail for each field force user today
        // Starting from Hamra area (Beirut) moving toward Achrafieh
        $trails = [
            // Trail 1: Hamra → Clemenceau → AUB → Bliss → Achrafieh direction
            [
                ['lat' => 33.8938, 'lng' => 35.4780, 'speed' => 0.0],    // Start at Fresh Mart Hamra
                ['lat' => 33.8935, 'lng' => 35.4800, 'speed' => 12.5],
                ['lat' => 33.8930, 'lng' => 35.4830, 'speed' => 18.0],
                ['lat' => 33.8925, 'lng' => 35.4870, 'speed' => 22.0],
                ['lat' => 33.8920, 'lng' => 35.4910, 'speed' => 15.0],
                ['lat' => 33.8915, 'lng' => 35.4950, 'speed' => 20.0],
                ['lat' => 33.8910, 'lng' => 35.4990, 'speed' => 0.0],    // Stop at a store
                ['lat' => 33.8905, 'lng' => 35.5030, 'speed' => 16.0],
                ['lat' => 33.8900, 'lng' => 35.5070, 'speed' => 24.0],
                ['lat' => 33.8896, 'lng' => 35.5110, 'speed' => 18.0],
                ['lat' => 33.8894, 'lng' => 35.5134, 'speed' => 0.0],    // At Quick Stop Achrafieh
            ],
            // Trail 2: Dora → Moving south toward Beirut center
            [
                ['lat' => 33.8882, 'lng' => 35.5572, 'speed' => 0.0],    // Start at MegaStore Dora
                ['lat' => 33.8880, 'lng' => 35.5540, 'speed' => 10.0],
                ['lat' => 33.8876, 'lng' => 35.5500, 'speed' => 22.0],
                ['lat' => 33.8870, 'lng' => 35.5460, 'speed' => 30.0],
                ['lat' => 33.8865, 'lng' => 35.5420, 'speed' => 25.0],
                ['lat' => 33.8860, 'lng' => 35.5380, 'speed' => 18.0],
                ['lat' => 33.8855, 'lng' => 35.5340, 'speed' => 20.0],
                ['lat' => 33.8850, 'lng' => 35.5300, 'speed' => 0.0],    // Stop
                ['lat' => 33.8845, 'lng' => 35.5260, 'speed' => 15.0],
                ['lat' => 33.8840, 'lng' => 35.5220, 'speed' => 28.0],
            ],
        ];

        foreach ($fieldUsers as $index => $user) {
            $trail = $trails[$index % count($trails)];
            $startTime = Carbon::today()->setHour(8)->setMinute(0);

            foreach ($trail as $pointIndex => $point) {
                GpsTrackingLog::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'route_instance_id' => $activeInstance?->id,
                    'latitude' => $point['lat'],
                    'longitude' => $point['lng'],
                    'accuracy_meters' => rand(3, 15),
                    'speed_kmh' => $point['speed'],
                    'bearing' => rand(0, 360),
                    'recorded_at' => $startTime->copy()->addMinutes($pointIndex * 15),
                ]);
            }
        }
    }
}
