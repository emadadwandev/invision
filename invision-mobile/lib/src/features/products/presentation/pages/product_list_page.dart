import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

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

    return Scaffold(
      appBar: AppBar(title: const Text('Products')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchController,
                    decoration: const InputDecoration(
                      hintText: 'Search by name, SKU, or barcode...',
                      prefixIcon: Icon(Icons.search),
                      border: OutlineInputBorder(),
                      isDense: true,
                    ),
                    onSubmitted: (_) => _onSearch(),
                  ),
                ),
                const SizedBox(width: 8),
                FilledButton(
                  onPressed: _onSearch,
                  child: const Text('Search'),
                ),
              ],
            ),
          ),
          Expanded(
            child: productsAsync.when(
              data: (products) => products.isEmpty
                  ? const Center(child: Text('No products found.'))
                  : RefreshIndicator(
                      onRefresh: () async =>
                          ref.invalidate(productsProvider(_filter)),
                      child: ListView.builder(
                        itemCount: products.length,
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        itemBuilder: (context, index) =>
                            _ProductCard(product: products[index]),
                      ),
                    ),
              loading: () => const Center(child: CircularProgressIndicator()),
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
    final theme = Theme.of(context);

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        title: Text(product.name, style: theme.textTheme.titleSmall),
        subtitle: Text(
          [
            if (product.sku != null) product.sku!,
            if (product.categoryName != null) product.categoryName!,
          ].join(' · '),
          style: theme.textTheme.bodySmall,
        ),
        trailing: Icon(
          Icons.circle,
          size: 12,
          color: product.isActive ? Colors.green : Colors.red,
        ),
        onTap: () => context.push('/products/${product.id}'),
      ),
    );
  }
}
