<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['name' => 'Lebanon', 'code' => 'LBN'],
            ['name' => 'United Arab Emirates', 'code' => 'ARE'],
            ['name' => 'Saudi Arabia', 'code' => 'SAU'],
            ['name' => 'Jordan', 'code' => 'JOR'],
            ['name' => 'Egypt', 'code' => 'EGY'],
            ['name' => 'Iraq', 'code' => 'IRQ'],
            ['name' => 'Kuwait', 'code' => 'KWT'],
            ['name' => 'Qatar', 'code' => 'QAT'],
            ['name' => 'Bahrain', 'code' => 'BHR'],
            ['name' => 'Oman', 'code' => 'OMN'],
        ];

        foreach ($countries as $country) {
            Country::firstOrCreate(
                ['code' => $country['code']],
                $country
            );
        }
    }
}
