<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPriceLevel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    // Categories

    public function listCategories(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ProductCategory::with('parent');

        if (! empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (! empty($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        if (isset($filters['root_only']) && $filters['root_only']) {
            $query->whereNull('parent_id');
        }

        return $query->orderBy('sort_order')->paginate($perPage);
    }

    public function categoryTree(): Collection
    {
        return ProductCategory::with('children.children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();
    }

    public function createCategory(array $data): ProductCategory
    {
        return ProductCategory::create($data);
    }

    public function updateCategory(ProductCategory $category, array $data): ProductCategory
    {
        $category->update($data);

        return $category;
    }

    public function deleteCategory(ProductCategory $category): void
    {
        $category->delete();
    }

    // Products

    public function listProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::with(['category', 'currentPrice']);

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('sku', 'like', "%{$filters['search']}%")
                  ->orWhere('barcode', 'like', "%{$filters['search']}%");
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->latest()->paginate($perPage);
    }

    public function createProduct(array $data): Product
    {
        $priceLevels = $data['price_levels'] ?? [];
        unset($data['price_levels']);

        $product = Product::create($data);

        foreach ($priceLevels as $priceLevel) {
            $product->priceLevels()->create($priceLevel);
        }

        return $product->load(['category', 'priceLevels']);
    }

    public function updateProduct(Product $product, array $data): Product
    {
        $priceLevels = $data['price_levels'] ?? null;
        unset($data['price_levels']);

        $product->update($data);

        if ($priceLevels !== null) {
            $product->priceLevels()->delete();
            foreach ($priceLevels as $priceLevel) {
                $product->priceLevels()->create($priceLevel);
            }
        }

        return $product->load(['category', 'priceLevels']);
    }

    public function deleteProduct(Product $product): void
    {
        $product->delete();
    }

    // Price Levels

    public function addPriceLevel(Product $product, array $data): ProductPriceLevel
    {
        return $product->priceLevels()->create($data);
    }

    public function updatePriceLevel(ProductPriceLevel $priceLevel, array $data): ProductPriceLevel
    {
        $priceLevel->update($data);

        return $priceLevel;
    }

    public function deletePriceLevel(ProductPriceLevel $priceLevel): void
    {
        $priceLevel->delete();
    }
}
