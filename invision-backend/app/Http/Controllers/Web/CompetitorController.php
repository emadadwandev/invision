<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Competitor;
use App\Models\CompetitorObservation;
use App\Models\CompetitorProduct;
use App\Models\Store;
use App\Services\CompetitorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompetitorController extends Controller
{
    public function __construct(private readonly CompetitorService $competitorService) {}

    // ─── Competitors ───────────────────────────────────────────

    public function index(Request $request): View
    {
        $competitors = $this->competitorService->listCompetitors(
            $request->only(['search', 'is_active']),
        );

        return view('pages.competitors.index', compact('competitors'));
    }

    public function create(): View
    {
        return view('pages.competitors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;

        $this->competitorService->createCompetitor($validated);

        return redirect()->route('competitors.index')->with('success', 'Competitor created successfully.');
    }

    public function show(Competitor $competitor): View
    {
        $competitor->load(['products', 'observations' => fn ($q) => $q->latest('observed_at')->limit(20)]);
        $competitor->loadCount(['products', 'observations']);

        return view('pages.competitors.show', compact('competitor'));
    }

    public function edit(Competitor $competitor): View
    {
        return view('pages.competitors.edit', compact('competitor'));
    }

    public function update(Request $request, Competitor $competitor): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $this->competitorService->updateCompetitor($competitor, $validated);

        return redirect()->route('competitors.show', $competitor)->with('success', 'Competitor updated successfully.');
    }

    public function destroy(Competitor $competitor): RedirectResponse
    {
        $this->competitorService->deleteCompetitor($competitor);

        return redirect()->route('competitors.index')->with('success', 'Competitor deleted successfully.');
    }

    // ─── Products ──────────────────────────────────────────────

    public function products(Request $request): View
    {
        $products = $this->competitorService->listProducts(
            $request->only(['search', 'competitor_id', 'category']),
        );
        $competitors = Competitor::query()->where('is_active', true)->orderBy('name')->get();

        return view('pages.competitors.products', compact('products', 'competitors'));
    }

    public function createProduct(): View
    {
        $competitors = Competitor::query()->where('is_active', true)->orderBy('name')->get();

        return view('pages.competitors.create-product', compact('competitors'));
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'competitor_id' => 'required|exists:competitors,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;

        $this->competitorService->createProduct($validated);

        return redirect()->route('competitors.products')->with('success', 'Competitor product created successfully.');
    }

    // ─── Observations ──────────────────────────────────────────

    public function observations(Request $request): View
    {
        $observations = $this->competitorService->listObservations(
            $request->only(['store_id', 'competitor_id', 'observation_type', 'from', 'to']),
        );
        $competitors = Competitor::query()->where('is_active', true)->orderBy('name')->get();
        $stores = Store::query()->where('is_active', true)->orderBy('name')->get();

        return view('pages.competitors.observations', compact('observations', 'competitors', 'stores'));
    }

    // ─── Analysis ──────────────────────────────────────────────

    public function analysis(Request $request): View
    {
        $data = $this->competitorService->competitorAnalysis(
            $request->only(['from', 'to', 'store_id']),
        );
        $stores = Store::query()->where('is_active', true)->orderBy('name')->get();

        return view('pages.competitors.analysis', compact('data', 'stores'));
    }
}
