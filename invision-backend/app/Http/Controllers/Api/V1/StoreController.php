<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\CreateStoreRequest;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreController extends Controller
{
    public function __construct(
        private readonly StoreService $storeService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Store::class);

        $stores = $this->storeService->list(
            $request->only(['search', 'category', 'rank', 'area_id', 'is_active']),
            $request->integer('per_page', 15)
        );

        return StoreResource::collection($stores);
    }

    public function store(CreateStoreRequest $request): JsonResponse
    {
        $this->authorize('create', Store::class);

        $store = $this->storeService->create($request->validated());

        return (new StoreResource($store))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Store $store): StoreResource
    {
        $this->authorize('view', $store);

        $store->load(['area', 'contacts', 'products']);

        return new StoreResource($store);
    }

    public function update(UpdateStoreRequest $request, Store $store): StoreResource
    {
        $this->authorize('update', $store);

        $store = $this->storeService->update($store, $request->validated());

        return new StoreResource($store);
    }

    public function destroy(Store $store): JsonResponse
    {
        $this->authorize('delete', $store);

        $this->storeService->delete($store);

        return response()->json(null, 204);
    }

    public function toggleActive(Store $store): StoreResource
    {
        $this->authorize('update', $store);

        $store = $this->storeService->toggleActive($store);

        return new StoreResource($store);
    }

    public function assignProducts(Request $request, Store $store): JsonResponse
    {
        $this->authorize('update', $store);

        $data = $request->validate([
            'product_ids' => ['required', 'array'],
            'product_ids.*' => ['exists:products,id'],
        ]);

        $this->storeService->assignProducts($store, $data['product_ids']);

        return response()->json(['message' => 'Products assigned successfully.']);
    }

    public function removeProducts(Request $request, Store $store): JsonResponse
    {
        $this->authorize('update', $store);

        $data = $request->validate([
            'product_ids' => ['required', 'array'],
            'product_ids.*' => ['exists:products,id'],
        ]);

        $this->storeService->removeProducts($store, $data['product_ids']);

        return response()->json(['message' => 'Products removed successfully.']);
    }
}
