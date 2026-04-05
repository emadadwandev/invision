<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\CreateProductCategoryRequest;
use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\UpdateProductCategoryRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductCategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    // Categories

    public function categories(Request $request): AnonymousResourceCollection
    {
        return ProductCategoryResource::collection(
            $this->productService->listCategories(
                $request->only(['search', 'parent_id', 'root_only']),
                $request->integer('per_page', 50)
            )
        );
    }

    public function categoryTree(): AnonymousResourceCollection
    {
        return ProductCategoryResource::collection(
            $this->productService->categoryTree()
        );
    }

    public function storeCategory(CreateProductCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $category = $this->productService->createCategory($request->validated());

        return (new ProductCategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    public function updateCategory(UpdateProductCategoryRequest $request, ProductCategory $category): ProductCategoryResource
    {
        $this->authorize('update', Product::class);

        $category = $this->productService->updateCategory($category, $request->validated());

        return new ProductCategoryResource($category);
    }

    public function destroyCategory(ProductCategory $category): JsonResponse
    {
        $this->authorize('delete', Product::class);

        $this->productService->deleteCategory($category);

        return response()->json(null, 204);
    }

    // Products

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Product::class);

        return ProductResource::collection(
            $this->productService->listProducts(
                $request->only(['search', 'category_id', 'is_active']),
                $request->integer('per_page', 15)
            )
        );
    }

    public function store(CreateProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $product = $this->productService->createProduct($request->validated());

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Product $product): ProductResource
    {
        $this->authorize('view', $product);

        $product->load(['category', 'priceLevels', 'currentPrice', 'stores']);

        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $this->authorize('update', $product);

        $product = $this->productService->updateProduct($product, $request->validated());

        return new ProductResource($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $this->productService->deleteProduct($product);

        return response()->json(null, 204);
    }
}
