<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Competitor;
use App\Models\CompetitorObservation;
use App\Models\CompetitorProduct;
use App\Services\CompetitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompetitorController extends Controller
{
    public function __construct(private readonly CompetitorService $competitorService) {}

    // ─── Competitors ───────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $competitors = $this->competitorService->listCompetitors(
            $request->only(['search', 'is_active']),
            $request->integer('per_page', 15),
        );

        return response()->json($competitors);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo_path' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;

        $competitor = $this->competitorService->createCompetitor($validated);

        return response()->json($competitor, 201);
    }

    public function show(Competitor $competitor): JsonResponse
    {
        $competitor->load(['products', 'observations' => fn ($q) => $q->latest('observed_at')->limit(10)]);
        $competitor->loadCount(['products', 'observations']);

        return response()->json($competitor);
    }

    public function update(Request $request, Competitor $competitor): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'logo_path' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $competitor = $this->competitorService->updateCompetitor($competitor, $validated);

        return response()->json($competitor);
    }

    public function destroy(Competitor $competitor): JsonResponse
    {
        $this->competitorService->deleteCompetitor($competitor);

        return response()->json(null, 204);
    }

    // ─── Competitor Products ───────────────────────────────────

    public function productIndex(Request $request): JsonResponse
    {
        $products = $this->competitorService->listProducts(
            $request->only(['search', 'competitor_id', 'category', 'is_active']),
            $request->integer('per_page', 15),
        );

        return response()->json($products);
    }

    public function productStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'competitor_id' => 'required|exists:competitors,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'image_path' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;

        $product = $this->competitorService->createProduct($validated);

        return response()->json($product->load('competitor'), 201);
    }

    public function productShow(CompetitorProduct $competitorProduct): JsonResponse
    {
        $competitorProduct->load('competitor');

        return response()->json($competitorProduct);
    }

    public function productUpdate(Request $request, CompetitorProduct $competitorProduct): JsonResponse
    {
        $validated = $request->validate([
            'competitor_id' => 'sometimes|exists:competitors,id',
            'name' => 'sometimes|string|max:255',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'image_path' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $product = $this->competitorService->updateProduct($competitorProduct, $validated);

        return response()->json($product);
    }

    public function productDestroy(CompetitorProduct $competitorProduct): JsonResponse
    {
        $this->competitorService->deleteProduct($competitorProduct);

        return response()->json(null, 204);
    }

    // ─── Observations ──────────────────────────────────────────

    public function observationIndex(Request $request): JsonResponse
    {
        $observations = $this->competitorService->listObservations(
            $request->only(['store_id', 'competitor_id', 'observation_type', 'user_id', 'store_visit_id', 'from', 'to']),
            $request->integer('per_page', 15),
        );

        return response()->json($observations);
    }

    public function observationStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_visit_id' => 'nullable|exists:store_visits,id',
            'store_id' => 'required|exists:stores,id',
            'competitor_id' => 'nullable|exists:competitors,id',
            'competitor_product_id' => 'nullable|exists:competitor_products,id',
            'observation_type' => 'required|string|in:sales,posm,pricing,display,promotion,stock_level,other',
            'quantity' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'photo_path' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'observed_at' => 'nullable|date',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;
        $validated['user_id'] = $request->user()->id;
        $validated['observed_at'] ??= now();

        $observation = $this->competitorService->createObservation($validated);

        return response()->json($observation->load(['store', 'competitor', 'competitorProduct']), 201);
    }

    public function observationShow(CompetitorObservation $competitorObservation): JsonResponse
    {
        $competitorObservation->load(['store', 'user', 'competitor', 'competitorProduct', 'storeVisit']);

        return response()->json($competitorObservation);
    }

    public function observationUpdate(Request $request, CompetitorObservation $competitorObservation): JsonResponse
    {
        $validated = $request->validate([
            'competitor_id' => 'nullable|exists:competitors,id',
            'competitor_product_id' => 'nullable|exists:competitor_products,id',
            'observation_type' => 'sometimes|string|in:sales,posm,pricing,display,promotion,stock_level,other',
            'quantity' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'photo_path' => 'nullable|string|max:500',
        ]);

        $observation = $this->competitorService->updateObservation($competitorObservation, $validated);

        return response()->json($observation);
    }

    public function observationDestroy(CompetitorObservation $competitorObservation): JsonResponse
    {
        $this->competitorService->deleteObservation($competitorObservation);

        return response()->json(null, 204);
    }

    // ─── Visit Observations ────────────────────────────────────

    public function visitObservations(int $storeVisitId): JsonResponse
    {
        $observations = $this->competitorService->getVisitObservations($storeVisitId);

        return response()->json(['data' => $observations]);
    }

    // ─── Analysis ──────────────────────────────────────────────

    public function analysis(Request $request): JsonResponse
    {
        $data = $this->competitorService->competitorAnalysis(
            $request->only(['from', 'to', 'store_id']),
        );

        return response()->json(['data' => $data]);
    }
}
