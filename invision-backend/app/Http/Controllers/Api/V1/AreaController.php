<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AreaResource;
use App\Http\Resources\CityResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\SectorResource;
use App\Http\Resources\StreetResource;
use App\Services\AreaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AreaController extends Controller
{
    public function __construct(
        private readonly AreaService $areaService,
    ) {}

    public function countries(): AnonymousResourceCollection
    {
        return CountryResource::collection($this->areaService->listCountries());
    }

    public function cities(Request $request): AnonymousResourceCollection
    {
        return CityResource::collection(
            $this->areaService->listCities($request->only(['country_id', 'search']))
        );
    }

    public function districts(Request $request): AnonymousResourceCollection
    {
        return DistrictResource::collection(
            $this->areaService->listDistricts($request->only(['city_id', 'search']))
        );
    }

    public function sectors(Request $request): AnonymousResourceCollection
    {
        return SectorResource::collection(
            $this->areaService->listSectors($request->only(['district_id', 'search']))
        );
    }

    public function areas(Request $request): AnonymousResourceCollection
    {
        return AreaResource::collection(
            $this->areaService->listAreas($request->only(['sector_id', 'search']))
        );
    }

    public function streets(Request $request): AnonymousResourceCollection
    {
        return StreetResource::collection(
            $this->areaService->listStreets($request->only(['area_id', 'search']))
        );
    }

    public function storeCountry(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:3|unique:countries,code',
        ]);

        $country = $this->areaService->createCountry($data);

        return (new CountryResource($country))
            ->response()
            ->setStatusCode(201);
    }

    public function storeCity(Request $request): JsonResponse
    {
        $data = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|string|max:255',
        ]);

        $city = $this->areaService->createCity($data);

        return (new CityResource($city))
            ->response()
            ->setStatusCode(201);
    }

    public function storeDistrict(Request $request): JsonResponse
    {
        $data = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name' => 'required|string|max:255',
        ]);

        $district = $this->areaService->createDistrict($data);

        return (new DistrictResource($district))
            ->response()
            ->setStatusCode(201);
    }

    public function storeSector(Request $request): JsonResponse
    {
        $data = $request->validate([
            'district_id' => 'required|exists:districts,id',
            'name' => 'required|string|max:255',
        ]);

        $sector = $this->areaService->createSector($data);

        return (new SectorResource($sector))
            ->response()
            ->setStatusCode(201);
    }

    public function storeArea(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sector_id' => 'required|exists:sectors,id',
            'name' => 'required|string|max:255',
            'gps_latitude' => 'nullable|numeric|between:-90,90',
            'gps_longitude' => 'nullable|numeric|between:-180,180',
            'radius_meters' => 'nullable|integer|min:0',
        ]);

        $area = $this->areaService->createArea($data);

        return (new AreaResource($area))
            ->response()
            ->setStatusCode(201);
    }

    public function storeStreet(Request $request): JsonResponse
    {
        $data = $request->validate([
            'area_id' => 'required|exists:areas,id',
            'name' => 'required|string|max:255',
        ]);

        $street = $this->areaService->createStreet($data);

        return (new StreetResource($street))
            ->response()
            ->setStatusCode(201);
    }
}
