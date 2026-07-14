import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/theme/app_theme.dart';
import '../../data/models/product_model.dart';
import '../../presentation/providers/product_providers.dart';

class ProductListPage extends ConsumerStatefulWidget {
  const ProductListPage({super.key});

  @override
  ConsumerState<ProductListPage> createState() => _ProductListPageState();
}

class _ProductListPageState extends ConsumerState<ProductListPage> {
  final _searchController = TextEditingController();
  ProductFilter _filter = const ProductFilter();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _onSearch() {
    setState(() {
      _filter = _filter.copyWith(search: _searchController.text);
    });
  }

  @override
  Widget build(BuildContext context) {
    final productsAsync = ref.watch(productsProvider(_filter));

    final tt = Theme.of(context).textTheme;
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Products', style: tt.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchController,
                    onSubmitted: (_) => _onSearch(),
                    decoration: const InputDecoration(
                      hintText: 'Search by name, SKU, or barcode...',
                      prefixIcon: Icon(Icons.search_rounded, color: AppColors.outline, size: 20),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                GestureDetector(
                  onTap: _onSearch,
                  child: Container(
                    width: 48, height: 48,
                    decoration: BoxDecoration(
                      color: AppColors.primary,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.search_rounded, color: Colors.white, size: 22),
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: productsAsync.when(
              data: (products) => products.isEmpty
                  ? Center(
                      child: Column(mainAxisSize: MainAxisSize.min, children: [
                        Container(
                          width: 64, height: 64,
                          decoration: BoxDecoration(
                            color: AppColors.surfaceContainerHigh,
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: const Icon(Icons.inventory_2_rounded, size: 32, color: AppColors.outline),
                        ),
                        const SizedBox(height: 12),
                        Text('No products found.',
                            style: tt.bodyLarge?.copyWith(color: AppColors.onSurfaceVariant)),
                      ]),
                    )
                  : RefreshIndicator(
                      color: AppColors.primary,
                      onRefresh: () async => ref.invalidate(productsProvider(_filter)),
                      child: ListView.builder(
                        itemCount: products.length,
                        padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
                        itemBuilder: (context, index) => _ProductCard(product: products[index]),
                      ),
                    ),
              loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
              error: (e, _) => Center(child: Text('Error: $e')),
            ),
          ),
        ],
      ),
    );
  }
}

class _ProductCard extends StatelessWidget {
  const _ProductCard({required this.product});

  final Product product;

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;
    return GestureDetector(
      onTap: () => context.push('/products/${product.id}'),
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surfaceContainerLowest,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
        ),
        child: Row(
          children: [
            Container(
              width: 44, height: 44,
              decoration: BoxDecoration(
                color: AppColors.surfaceContainerLow,
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(Icons.inventory_2_rounded, color: AppColors.primary, size: 22),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(product.name,
                      style: tt.titleSmall?.copyWith(
                          color: AppColors.onSurface, fontWeight: FontWeight.w700)),
                  const SizedBox(height: 3),
                  Text(
                    [
                      if (product.sku != null) product.sku!,
                      if (product.categoryName != null) product.categoryName!,
                    ].join('  ·  '),
                    style: tt.bodySmall?.copyWith(color: AppColors.onSurfaceVariant),
                  ),
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: product.isActive ? AppColors.secondaryContainer : AppColors.errorContainer,
                borderRadius: BorderRadius.circular(100),
              ),
              child: Text(
                product.isActive ? 'Active' : 'Inactive',
                style: TextStyle(
                    fontSize: 10, fontWeight: FontWeight.w700,
                    color: product.isActive ? AppColors.onSecondaryContainer : AppColors.onErrorContainer),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
