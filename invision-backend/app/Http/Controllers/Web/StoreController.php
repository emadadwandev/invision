<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\CreateStoreRequest;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Models\Area;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function __construct(
        private readonly StoreService $storeService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Store::class);

        $stores = $this->storeService->list(
            $request->only(['search', 'category', 'rank', 'area_id', 'is_active']),
            $request->integer('per_page', 15)
        );

        return view('pages.stores.index', compact('stores'));
    }

    public function create(): View
    {
        $this->authorize('create', Store::class);

        $areas = Area::where('is_active', true)->get();

        return view('pages.stores.create', compact('areas'));
    }

    public function store(CreateStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Store::class);

        $this->storeService->create($request->validated());

        return redirect()->route('stores.index')
            ->with('success', 'Store created successfully.');
    }

    public function show(Store $store): View
    {
        $this->authorize('view', $store);

        $store->load(['area', 'contacts', 'products.category']);

        return view('pages.stores.show', compact('store'));
    }

    public function edit(Store $store): View
    {
        $this->authorize('update', $store);

        $areas = Area::where('is_active', true)->get();
        $store->load('contacts');

        return view('pages.stores.edit', compact('store', 'areas'));
    }

    public function update(UpdateStoreRequest $request, Store $store): RedirectResponse
    {
        $this->authorize('update', $store);

        $this->storeService->update($store, $request->validated());

        return redirect()->route('stores.show', $store)
            ->with('success', 'Store updated successfully.');
    }

    public function destroy(Store $store): RedirectResponse
    {
        $this->authorize('delete', $store);

        $this->storeService->delete($store);

        return redirect()->route('stores.index')
            ->with('success', 'Store deleted successfully.');
    }

    public function assignProductsView(Store $store): View
    {
        $this->authorize('update', $store);

        $assignedIds = $store->products()->pluck('products.id')->toArray();
        $categories = ProductCategory::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        return view('pages.stores.assign-products', compact('store', 'products', 'categories', 'assignedIds'));
    }

    public function syncProducts(Request $request, Store $store): RedirectResponse
    {
        $this->authorize('update', $store);

        $productIds = $request->input('product_ids', []);

        $syncData = collect($productIds)
            ->mapWithKeys(fn ($id) => [(int) $id => ['is_active' => true]])
            ->all();

        $store->products()->sync($syncData);

        return redirect()->route('stores.show', $store)
            ->with('success', 'Products updated successfully.');
    }
}
