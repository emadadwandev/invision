<?php

namespace Database\Seeders;

use App\Enums\StoreCategory;
use App\Enums\StoreRank;
use App\Models\Area;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPriceLevel;
use App\Models\Store;
use App\Models\StoreContact;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class StoreCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->where('slug', 'invision-default')->first();

        if (! $tenant) {
            return;
        }

        $area = Area::query()->where('tenant_id', $tenant->id)->first();

        // --- Product Categories ---
        $categories = [];
        foreach ($this->categoryTree() as $parentName => $children) {
            $parent = ProductCategory::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $parentName],
                ['sort_order' => 0, 'is_active' => true]
            );
            $categories[$parentName] = $parent;

            foreach ($children as $i => $childName) {
                $child = ProductCategory::query()->firstOrCreate(
                    ['tenant_id' => $tenant->id, 'name' => $childName],
                    ['parent_id' => $parent->id, 'sort_order' => $i + 1, 'is_active' => true]
                );
                $categories[$childName] = $child;
            }
        }

        // --- Products ---
        $products = [];
        foreach ($this->productList() as $item) {
            $product = Product::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'sku' => $item['sku']],
                [
                    'category_id' => $categories[$item['category']]->id,
                    'name' => $item['name'],
                    'barcode' => $item['barcode'] ?? null,
                    'description' => $item['description'] ?? null,
                    'is_active' => true,
                ]
            );

            foreach ($item['prices'] as $level) {
                ProductPriceLevel::query()->firstOrCreate(
                    ['product_id' => $product->id, 'level_name' => $level['level']],
                    [
                        'price' => $level['price'],
                        'effective_from' => now()->startOfYear(),
                    ]
                );
            }

            $products[] = $product;
        }

        // --- Stores ---
        foreach ($this->storeList() as $storeData) {
            $store = Store::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'code' => $storeData['code']],
                [
                    'name' => $storeData['name'],
                    'category' => $storeData['category'],
                    'rank' => $storeData['rank'],
                    'gps_latitude' => $storeData['lat'],
                    'gps_longitude' => $storeData['lng'],
                    'address' => $storeData['address'],
                    'area_id' => $area?->id,
                    'is_active' => true,
                ]
            );

            // contacts
            if ($store->wasRecentlyCreated) {
                StoreContact::query()->create([
                    'store_id' => $store->id,
                    'name' => $storeData['contact'],
                    'phone' => '+961 ' . rand(70, 79) . ' ' . rand(100000, 999999),
                    'position' => 'Manager',
                    'is_primary' => true,
                ]);

                // assign random products
                $randomProducts = collect($products)->random(min(count($products), rand(3, 6)));
                $store->products()->syncWithoutDetaching(
                    $randomProducts->pluck('id')->mapWithKeys(fn ($id) => [$id => ['is_active' => true]])->all()
                );
            }
        }
    }

    private function categoryTree(): array
    {
        return [
            'Beverages' => ['Soft Drinks', 'Juices', 'Water', 'Energy Drinks'],
            'Dairy' => ['Milk', 'Cheese', 'Yogurt'],
            'Snacks' => ['Chips', 'Biscuits', 'Chocolate'],
            'Personal Care' => ['Shampoo', 'Soap', 'Toothpaste'],
            'Household' => ['Detergent', 'Cleaning Supplies'],
        ];
    }

    private function productList(): array
    {
        return [
            ['name' => 'Cola 330ml Can', 'sku' => 'BEV-001', 'barcode' => '5901234123457', 'category' => 'Soft Drinks', 'description' => 'Carbonated soft drink 330ml', 'prices' => [['level' => 'Retail', 'price' => 0.75], ['level' => 'Wholesale', 'price' => 0.55]]],
            ['name' => 'Orange Juice 1L', 'sku' => 'BEV-002', 'barcode' => '5901234123464', 'category' => 'Juices', 'description' => 'Fresh orange juice 1 liter', 'prices' => [['level' => 'Retail', 'price' => 2.50], ['level' => 'Wholesale', 'price' => 1.90]]],
            ['name' => 'Spring Water 500ml', 'sku' => 'BEV-003', 'category' => 'Water', 'prices' => [['level' => 'Retail', 'price' => 0.50]]],
            ['name' => 'Full-Fat Milk 1L', 'sku' => 'DAI-001', 'category' => 'Milk', 'prices' => [['level' => 'Retail', 'price' => 1.80], ['level' => 'Wholesale', 'price' => 1.40]]],
            ['name' => 'White Cheese 250g', 'sku' => 'DAI-002', 'category' => 'Cheese', 'prices' => [['level' => 'Retail', 'price' => 3.00]]],
            ['name' => 'Plain Yogurt 500g', 'sku' => 'DAI-003', 'category' => 'Yogurt', 'prices' => [['level' => 'Retail', 'price' => 1.50]]],
            ['name' => 'Potato Chips 150g', 'sku' => 'SNK-001', 'barcode' => '5901234123471', 'category' => 'Chips', 'prices' => [['level' => 'Retail', 'price' => 1.25], ['level' => 'Wholesale', 'price' => 0.95]]],
            ['name' => 'Chocolate Bar 50g', 'sku' => 'SNK-002', 'category' => 'Chocolate', 'prices' => [['level' => 'Retail', 'price' => 1.00]]],
            ['name' => 'Shampoo 400ml', 'sku' => 'PC-001', 'category' => 'Shampoo', 'prices' => [['level' => 'Retail', 'price' => 4.50]]],
            ['name' => 'Laundry Detergent 3L', 'sku' => 'HH-001', 'category' => 'Detergent', 'description' => 'Liquid laundry detergent', 'prices' => [['level' => 'Retail', 'price' => 6.00], ['level' => 'Wholesale', 'price' => 4.80]]],
        ];
    }

    private function storeList(): array
    {
        return [
            ['name' => 'Fresh Mart Hamra', 'code' => 'STR-001', 'category' => StoreCategory::Supermarket, 'rank' => StoreRank::Gold, 'lat' => 33.8938, 'lng' => 35.4780, 'address' => 'Hamra Street, Beirut', 'contact' => 'Ahmad Khoury'],
            ['name' => 'Quick Stop Achrafieh', 'code' => 'STR-002', 'category' => StoreCategory::ConvenienceStore, 'rank' => StoreRank::Silver, 'lat' => 33.8894, 'lng' => 35.5134, 'address' => 'Sassine Square, Achrafieh', 'contact' => 'Nadia Haddad'],
            ['name' => 'MegaStore Dora', 'code' => 'STR-003', 'category' => StoreCategory::Hypermarket, 'rank' => StoreRank::Platinum, 'lat' => 33.8882, 'lng' => 35.5572, 'address' => 'Dora Highway, Metn', 'contact' => 'Georges Nassif'],
            ['name' => 'Pharma Plus Verdun', 'code' => 'STR-004', 'category' => StoreCategory::Pharmacy, 'rank' => StoreRank::Bronze, 'lat' => 33.8768, 'lng' => 35.4830, 'address' => 'Verdun Street, Beirut', 'contact' => 'Layla Farah'],
            ['name' => 'Mini Market Jounieh', 'code' => 'STR-005', 'category' => StoreCategory::MiniMarket, 'rank' => StoreRank::Silver, 'lat' => 33.9812, 'lng' => 35.6177, 'address' => 'Main Road, Jounieh', 'contact' => 'Tony Saad'],
        ];
    }
}
