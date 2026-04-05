<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\City;
use App\Models\Country;
use App\Models\District;
use App\Models\Sector;
use App\Models\Street;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class GeographySeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->where('slug', 'invision-default')->first();

        if (! $tenant) {
            return;
        }

        foreach ($this->geographyMap() as $countryCode => $countryData) {
            $country = Country::query()->firstOrCreate(
                ['code' => $countryCode],
                [
                    'name' => $countryData['name'],
                    'is_active' => true,
                ]
            );

            foreach ($countryData['cities'] as $cityData) {
                $city = City::query()->firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'country_id' => $country->id,
                        'name' => $cityData['name'],
                    ],
                    ['is_active' => true]
                );

                foreach ($cityData['districts'] as $districtData) {
                    $district = District::query()->firstOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'city_id' => $city->id,
                            'name' => $districtData['name'],
                        ],
                        ['is_active' => true]
                    );

                    foreach ($districtData['sectors'] as $sectorData) {
                        $sector = Sector::query()->firstOrCreate(
                            [
                                'tenant_id' => $tenant->id,
                                'district_id' => $district->id,
                                'name' => $sectorData['name'],
                            ],
                            ['is_active' => true]
                        );

                        foreach ($sectorData['areas'] as $areaData) {
                            $area = Area::query()->firstOrCreate(
                                [
                                    'tenant_id' => $tenant->id,
                                    'sector_id' => $sector->id,
                                    'name' => $areaData['name'],
                                ],
                                [
                                    'gps_latitude' => $areaData['gps_latitude'],
                                    'gps_longitude' => $areaData['gps_longitude'],
                                    'radius_meters' => $areaData['radius_meters'],
                                    'is_active' => true,
                                ]
                            );

                            foreach ($areaData['streets'] as $streetName) {
                                Street::query()->firstOrCreate(
                                    [
                                        'tenant_id' => $tenant->id,
                                        'area_id' => $area->id,
                                        'name' => $streetName,
                                    ],
                                    ['is_active' => true]
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    private function geographyMap(): array
    {
        return [
            'LBN' => [
                'name' => 'Lebanon',
                'cities' => [
                    [
                        'name' => 'Beirut',
                        'districts' => [
                            [
                                'name' => 'Beirut Central',
                                'sectors' => [
                                    [
                                        'name' => 'Hamra Sector',
                                        'areas' => [
                                            [
                                                'name' => 'Hamra Area',
                                                'gps_latitude' => 33.89720000,
                                                'gps_longitude' => 35.48240000,
                                                'radius_meters' => 500,
                                                'streets' => ['Bliss Street', 'Makdessi Street'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => 'Tripoli',
                        'districts' => [
                            [
                                'name' => 'Tripoli District',
                                'sectors' => [
                                    [
                                        'name' => 'El Mina Sector',
                                        'areas' => [
                                            [
                                                'name' => 'Mina Area',
                                                'gps_latitude' => 34.44460000,
                                                'gps_longitude' => 35.81700000,
                                                'radius_meters' => 600,
                                                'streets' => ['Port Road', 'Sea View Street'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'ARE' => [
                'name' => 'United Arab Emirates',
                'cities' => [
                    [
                        'name' => 'Dubai',
                        'districts' => [
                            [
                                'name' => 'Dubai Urban District',
                                'sectors' => [
                                    [
                                        'name' => 'Marina Sector',
                                        'areas' => [
                                            [
                                                'name' => 'Dubai Marina Area',
                                                'gps_latitude' => 25.08200000,
                                                'gps_longitude' => 55.14000000,
                                                'radius_meters' => 700,
                                                'streets' => ['Marina Walk', 'Al Marsa Street'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => 'Abu Dhabi',
                        'districts' => [
                            [
                                'name' => 'Abu Dhabi Central District',
                                'sectors' => [
                                    [
                                        'name' => 'Corniche Sector',
                                        'areas' => [
                                            [
                                                'name' => 'Corniche Area',
                                                'gps_latitude' => 24.46670000,
                                                'gps_longitude' => 54.36670000,
                                                'radius_meters' => 800,
                                                'streets' => ['Corniche Road', 'Khalidiya Street'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
