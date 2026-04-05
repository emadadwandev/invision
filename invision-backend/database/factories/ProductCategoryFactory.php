<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProductCategory> */
class ProductCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->unique()->word() . ' Category',
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }

    public function child(ProductCategory $parent): static
    {
        return $this->state(fn () => [
            'tenant_id' => $parent->tenant_id,
            'parent_id' => $parent->id,
        ]);
    }
}
