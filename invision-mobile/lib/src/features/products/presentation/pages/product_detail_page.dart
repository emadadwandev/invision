import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../data/models/product_model.dart';
import '../../presentation/providers/product_providers.dart';

class ProductDetailPage extends ConsumerWidget {
  const ProductDetailPage({required this.productId, super.key});

  final int productId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final productAsync = ref.watch(productDetailProvider(productId));

    return Scaffold(
      appBar: AppBar(title: const Text('Product Details')),
      body: productAsync.when(
        data: (product) => _ProductDetailBody(product: product),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}

class _ProductDetailBody extends StatelessWidget {
  const _ProductDetailBody({required this.product});

  final Product product;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Header
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(product.name,
                          style: theme.textTheme.headlineSmall),
                    ),
                    Chip(
                      label: Text(product.isActive ? 'Active' : 'Inactive'),
                      backgroundColor:
                          product.isActive ? Colors.green[50] : Colors.red[50],
                      labelStyle: TextStyle(
                        color: product.isActive ? Colors.green : Colors.red,
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
                if (product.sku != null) ...[
                  const SizedBox(height: 4),
                  Text('SKU: ${product.sku}',
                      style: theme.textTheme.bodyMedium),
                ],
              ],
            ),
          ),
        ),
        const SizedBox(height: 12),

        // Info
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Information', style: theme.textTheme.titleMedium),
                const Divider(),
                _InfoRow('Category', product.categoryName ?? '—'),
                if (product.barcode != null)
                  _InfoRow('Barcode', product.barcode!),
                if (product.description != null) ...[
                  const SizedBox(height: 8),
                  Text('Description',
                      style: theme.textTheme.bodySmall
                          ?.copyWith(color: Colors.grey[600])),
                  const SizedBox(height: 4),
                  Text(product.description!),
                ],
              ],
            ),
          ),
        ),

        // Price levels
        if (product.priceLevels.isNotEmpty) ...[
          const SizedBox(height: 12),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Price Levels', style: theme.textTheme.titleMedium),
                  const Divider(),
                  ...product.priceLevels.map(
                    (p) => Padding(
                      padding: const EdgeInsets.symmetric(vertical: 4),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(p.levelName),
                          Text(
                            '\$${p.price.toStringAsFixed(2)}',
                            style: theme.textTheme.titleSmall,
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ],
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow(this.label, this.value);

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          SizedBox(
            width: 100,
            child: Text(label,
                style: Theme.of(context)
                    .textTheme
                    .bodySmall
                    ?.copyWith(color: Colors.grey[600])),
          ),
          Expanded(child: Text(value)),
        ],
      ),
    );
  }
}
