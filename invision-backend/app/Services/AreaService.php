<?php

namespace App\Services;

use App\Models\Area;
use App\Models\City;
use App\Models\Country;
use App\Models\District;
use App\Models\Sector;
use App\Models\Street;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AreaService
{
    public function listCountries(): LengthAwarePaginator
    {
        return Country::where('is_active', true)->paginate(50);
    }

    public function listCities(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = City::with('country');

        if (! empty($filters['country_id'])) {
            $query->where('country_id', $filters['country_id']);
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        return $query->paginate($perPage);
    }

    public function listDistricts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = District::with('city');

        if (! empty($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        return $query->paginate($perPage);
    }

    public function listSectors(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Sector::with('district');

        if (! empty($filters['district_id'])) {
            $query->where('district_id', $filters['district_id']);
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        return $query->paginate($perPage);
    }

    public function listAreas(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Area::with('sector');

        if (! empty($filters['sector_id'])) {
            $query->where('sector_id', $filters['sector_id']);
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        return $query->paginate($perPage);
    }

    public function listStreets(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Street::with('area');

        if (! empty($filters['area_id'])) {
            $query->where('area_id', $filters['area_id']);
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        return $query->paginate($perPage);
    }

    public function createCountry(array $data): Country
    {
        return Country::create($data);
    }

    public function createCity(array $data): City
    {
        return City::create($data);
    }

    public function createDistrict(array $data): District
    {
        return District::create($data);
    }

    public function createSector(array $data): Sector
    {
        return Sector::create($data);
    }

    public function createArea(array $data): Area
    {
        return Area::create($data);
    }

    public function createStreet(array $data): Street
    {
        return Street::create($data);
    }
}
