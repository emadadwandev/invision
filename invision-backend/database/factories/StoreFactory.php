<?php

namespace Database\Factories;

use App\Enums\StoreCategory;
use App\Enums\StoreRank;
use App\Models\Area;
use App\Models\Store;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Store> */
class StoreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->company(),
            'code' => fake()->unique()->bothify('STR-####'),
            'category' => fake()->randomElement(StoreCategory::cases()),
            'rank' => fake()->randomElement(StoreRank::cases()),
            'gps_latitude' => fake()->latitude(33.8, 34.0),
            'gps_longitude' => fake()->longitude(35.4, 35.6),
            'address' => fake()->address(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function category(StoreCategory $cat): static
    {
        return $this->state(fn () => ['category' => $cat]);
    }

    public function rank(StoreRank $rank): static
    {
        return $this->state(fn () => ['rank' => $rank]);
    }
}
