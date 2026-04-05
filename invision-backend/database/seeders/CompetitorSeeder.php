<?php

namespace Database\Seeders;

use App\Enums\ObservationType;
use App\Models\Competitor;
use App\Models\CompetitorObservation;
use App\Models\CompetitorProduct;
use App\Models\Store;
use App\Models\StoreVisit;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompetitorSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->where('slug', 'invision-default')->first();

        if (! $tenant) {
            return;
        }

        $stores = Store::query()->where('tenant_id', $tenant->id)->get();
        $users = User::query()->where('tenant_id', $tenant->id)->get();

        if ($stores->isEmpty() || $users->isEmpty()) {
            return;
        }

        // ─── Competitors ───────────────────────────────────────

        $competitors = [
            ['name' => 'CompetitorX Corp', 'description' => 'Major competitor in the electronics sector'],
            ['name' => 'RivalBrand Inc', 'description' => 'Competing brand in consumer goods'],
            ['name' => 'MarketLeader Co', 'description' => 'Industry leader in retail products'],
        ];

        foreach ($competitors as $compData) {
            $competitor = Competitor::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $compData['name']],
                array_merge($compData, ['tenant_id' => $tenant->id, 'is_active' => true]),
            );

            if (! $competitor->wasRecentlyCreated) {
                continue;
            }

            // ─── Products per competitor ───────────────────────

            $products = [];
            for ($i = 1; $i <= 3; $i++) {
                $products[] = CompetitorProduct::create([
                    'tenant_id' => $tenant->id,
                    'competitor_id' => $competitor->id,
                    'name' => $competitor->name . ' Product ' . $i,
                    'sku' => 'COMP-' . strtoupper(substr($competitor->name, 0, 3)) . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'barcode' => '999' . $competitor->id . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'category' => ['Electronics', 'FMCG', 'Beverages'][$i - 1],
                    'is_active' => true,
                ]);
            }

            // ─── Observations per competitor ───────────────────

            $types = ObservationType::cases();
            $visits = StoreVisit::query()->where('tenant_id', $tenant->id)->limit(5)->get();

            for ($j = 0; $j < 5; $j++) {
                $store = $stores->random();
                $user = $users->random();
                $type = $types[array_rand($types)];
                $product = $products[array_rand($products)];

                CompetitorObservation::create([
                    'tenant_id' => $tenant->id,
                    'store_visit_id' => $visits->isNotEmpty() ? $visits->random()->id : null,
                    'store_id' => $store->id,
                    'user_id' => $user->id,
                    'competitor_id' => $competitor->id,
                    'competitor_product_id' => $product->id,
                    'observation_type' => $type->value,
                    'quantity' => rand(1, 100),
                    'price' => rand(500, 5000) / 100,
                    'notes' => 'Observed ' . $type->label() . ' activity at ' . $store->name,
                    'latitude' => $store->gps_latitude,
                    'longitude' => $store->gps_longitude,
                    'observed_at' => now()->subDays(rand(0, 30)),
                ]);
            }
        }
    }
}
