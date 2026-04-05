<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\CreateProductCategoryRequest;
use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\UpdateProductCategoryRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    // Categories

    public function categories(Request $request): View
    {
        $categories = $this->productService->listCategories(
            $request->only(['search', 'parent_id']),
            $request->integer('per_page', 30)
        );

        return view('pages.products.categories.index', compact('categories'));
    }

    public function createCategory(): View
    {
        $this->authorize('create', Product::class);

        $parentCategories = ProductCategory::whereNull('parent_id')
            ->where('is_active', true)
            ->get();

        return view('pages.products.categories.create', compact('parentCategories'));
    }

    public function storeCategory(CreateProductCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $this->productService->createCategory($request->validated());

        return redirect()->route('product-categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function editCategory(ProductCategory $category): View
    {
        $this->authorize('update', Product::class);

        $parentCategories = ProductCategory::whereNull('parent_id')
            ->where('is_active', true)
            ->where('id', '!=', $category->id)
            ->get();

        return view('pages.products.categories.edit', compact('category', 'parentCategories'));
    }

    public function updateCategory(UpdateProductCategoryRequest $request, ProductCategory $category): RedirectResponse
    {
        $this->authorize('update', Product::class);

        $this->productService->updateCategory($category, $request->validated());

        return redirect()->route('product-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroyCategory(ProductCategory $category): RedirectResponse
    {
        $this->authorize('delete', Product::class);

        $this->productService->deleteCategory($category);

        return redirect()->route('product-categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    // Products

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $products = $this->productService->listProducts(
            $request->only(['search', 'category_id', 'is_active']),
            $request->integer('per_page', 15)
        );

        $categories = ProductCategory::where('is_active', true)->get();

        return view('pages.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        $categories = ProductCategory::where('is_active', true)->get();

        return view('pages.products.create', compact('categories'));
    }

    public function store(CreateProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $this->productService->createProduct($request->validated());

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product): View
    {
        $this->authorize('view', $product);

        $product->load(['category', 'priceLevels', 'stores']);

        return view('pages.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        $product->load('priceLevels');
        $categories = ProductCategory::where('is_active', true)->get();

        return view('pages.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $this->productService->updateProduct($product, $request->validated());

        return redirect()->route('products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->productService->deleteProduct($product);

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
