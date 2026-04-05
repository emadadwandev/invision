<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            DefaultTenantSeeder::class,
            PermissionSeeder::class,
            CountrySeeder::class,
            GeographySeeder::class,
            StoreCatalogSeeder::class,
            RouteSeeder::class,
            CampaignSeeder::class,
            SalesSeeder::class,
            PosSeeder::class,
            NotificationSeeder::class,
            CommandCenterSeeder::class,
            CompetitorSeeder::class,
        ]);
    }
}
