<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\City;
use App\Models\Country;
use App\Models\District;
use App\Models\Sector;
use App\Models\Street;
use App\Services\AreaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GeographyController extends Controller
{
    public function __construct(
        private readonly AreaService $areaService,
    ) {}

    // ─── Index (Hierarchy page) ───────────────────────────────

    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'cities');

        $countries = Country::where('is_active', true)->orderBy('name')->get();
        $cities = City::with('country')->orderBy('name')->paginate(20, ['*'], 'cities_page');
        $districts = District::with('city')->orderBy('name')->paginate(20, ['*'], 'districts_page');
        $sectors = Sector::with('district')->orderBy('name')->paginate(20, ['*'], 'sectors_page');
        $areas = Area::with('sector')->orderBy('name')->paginate(20, ['*'], 'areas_page');

        return view('pages.geography.index', compact('tab', 'countries', 'cities', 'districts', 'sectors', 'areas'));
    }

    // ─── City CRUD ────────────────────────────────────────────

    public function createCity(): View
    {
        $countries = Country::where('is_active', true)->orderBy('name')->get();

        return view('pages.geography.create-city', compact('countries'));
    }

    public function storeCity(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'exists:countries,id'],
        ]);

        $this->areaService->createCity($data);

        return redirect()->route('geography.index', ['tab' => 'cities'])
            ->with('success', 'City created successfully.');
    }

    public function updateCity(Request $request, City $city): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $city->update($data);

        return redirect()->route('geography.index', ['tab' => 'cities'])
            ->with('success', 'City updated successfully.');
    }

    public function destroyCity(City $city): RedirectResponse
    {
        $city->delete();

        return redirect()->route('geography.index', ['tab' => 'cities'])
            ->with('success', 'City deleted successfully.');
    }

    // ─── District CRUD ────────────────────────────────────────

    public function createDistrict(): View
    {
        $cities = City::where('is_active', true)->orderBy('name')->get();

        return view('pages.geography.create-district', compact('cities'));
    }

    public function storeDistrict(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'city_id' => ['required', 'exists:cities,id'],
        ]);

        $this->areaService->createDistrict($data);

        return redirect()->route('geography.index', ['tab' => 'districts'])
            ->with('success', 'District created successfully.');
    }

    public function updateDistrict(Request $request, District $district): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $district->update($data);

        return redirect()->route('geography.index', ['tab' => 'districts'])
            ->with('success', 'District updated successfully.');
    }

    public function destroyDistrict(District $district): RedirectResponse
    {
        $district->delete();

        return redirect()->route('geography.index', ['tab' => 'districts'])
            ->with('success', 'District deleted successfully.');
    }

    // ─── Sector (Zone) CRUD ───────────────────────────────────

    public function createSector(): View
    {
        $districts = District::where('is_active', true)->orderBy('name')->get();

        return view('pages.geography.create-sector', compact('districts'));
    }

    public function storeSector(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'district_id' => ['required', 'exists:districts,id'],
        ]);

        $this->areaService->createSector($data);

        return redirect()->route('geography.index', ['tab' => 'sectors'])
            ->with('success', 'Sector created successfully.');
    }

    public function updateSector(Request $request, Sector $sector): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $sector->update($data);

        return redirect()->route('geography.index', ['tab' => 'sectors'])
            ->with('success', 'Sector updated successfully.');
    }

    public function destroySector(Sector $sector): RedirectResponse
    {
        $sector->delete();

        return redirect()->route('geography.index', ['tab' => 'sectors'])
            ->with('success', 'Sector deleted successfully.');
    }

    // ─── Area CRUD ────────────────────────────────────────────

    public function createArea(): View
    {
        $sectors = Sector::where('is_active', true)->orderBy('name')->get();

        return view('pages.geography.create-area', compact('sectors'));
    }

    public function storeArea(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sector_id' => ['required', 'exists:sectors,id'],
            'gps_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_meters' => ['nullable', 'integer', 'min:1'],
        ]);

        $this->areaService->createArea($data);

        return redirect()->route('geography.index', ['tab' => 'areas'])
            ->with('success', 'Area created successfully.');
    }

    public function updateArea(Request $request, Area $area): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'gps_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_meters' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $area->update($data);

        return redirect()->route('geography.index', ['tab' => 'areas'])
            ->with('success', 'Area updated successfully.');
    }

    public function destroyArea(Area $area): RedirectResponse
    {
        $area->delete();

        return redirect()->route('geography.index', ['tab' => 'areas'])
            ->with('success', 'Area deleted successfully.');
    }

    // ─── JSON endpoints for cascading dropdowns ───────────────

    public function citiesByCountry(Country $country): JsonResponse
    {
        return response()->json(
            City::where('country_id', $country->id)->where('is_active', true)->orderBy('name')->get(['id', 'name'])
        );
    }

    public function districtsByCity(City $city): JsonResponse
    {
        return response()->json(
            District::where('city_id', $city->id)->where('is_active', true)->orderBy('name')->get(['id', 'name'])
        );
    }

    public function sectorsByDistrict(District $district): JsonResponse
    {
        return response()->json(
            Sector::where('district_id', $district->id)->where('is_active', true)->orderBy('name')->get(['id', 'name'])
        );
    }

    public function areasBySector(Sector $sector): JsonResponse
    {
        return response()->json(
            Area::where('sector_id', $sector->id)->where('is_active', true)->orderBy('name')->get(['id', 'name'])
        );
    }
}
