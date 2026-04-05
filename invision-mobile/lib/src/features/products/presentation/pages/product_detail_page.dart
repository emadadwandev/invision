import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/theme/app_theme.dart';
import '../../data/models/product_model.dart';
import '../../presentation/providers/product_providers.dart';

class ProductDetailPage extends ConsumerWidget {
  const ProductDetailPage({required this.productId, super.key});

  final int productId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final productAsync = ref.watch(productDetailProvider(productId));

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Product Details',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: productAsync.when(
        data: (product) => _ProductDetailBody(product: product),
        loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
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
    final tt = Theme.of(context).textTheme;
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Header gradient card
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [AppColors.primary, AppColors.primaryContainer],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                      child: Text(product.name,
                          style: tt.headlineSmall?.copyWith(color: Colors.white))),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                    decoration: BoxDecoration(
                      color: product.isActive
                          ? Colors.white.withOpacity(0.25)
                          : Colors.red.withOpacity(0.3),
                      borderRadius: BorderRadius.circular(100),
                    ),
                    child: Text(
                      product.isActive ? 'Active' : 'Inactive',
                      style: const TextStyle(
                          color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700),
                    ),
                  ),
                ],
              ),
              if (product.sku != null) ...[const SizedBox(height: 6),
                Text('SKU: ${product.sku}', style: tt.bodyMedium?.copyWith(color: Colors.white70))],
            ],
          ),
        ),
        const SizedBox(height: 14),

        // Info
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: AppColors.surfaceContainerLowest,
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Information', style: tt.titleMedium?.copyWith(color: AppColors.onSurface)),
              const SizedBox(height: 12),
              Divider(color: AppColors.outlineVariant.withOpacity(0.5), height: 1),
              const SizedBox(height: 12),
              _InfoRow('Category', product.categoryName ?? '—'),
              if (product.barcode != null) _InfoRow('Barcode', product.barcode!),
              if (product.description != null) ...[const SizedBox(height: 10),
                Text('Description',
                    style: tt.bodySmall?.copyWith(color: AppColors.onSurfaceVariant)),
                const SizedBox(height: 6),
                Text(product.description!,
                    style: tt.bodyMedium?.copyWith(color: AppColors.onSurface))],
            ],
          ),
        ),

        // Price levels
        if (product.priceLevels.isNotEmpty) ...[const SizedBox(height: 14),
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: AppColors.surfaceContainerLowest,
              borderRadius: BorderRadius.circular(16),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Price Levels', style: tt.titleMedium?.copyWith(color: AppColors.onSurface)),
                const SizedBox(height: 12),
                Divider(color: AppColors.outlineVariant.withOpacity(0.5), height: 1),
                const SizedBox(height: 8),
                ...product.priceLevels.map(
                  (p) => Padding(
                    padding: const EdgeInsets.symmetric(vertical: 6),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(p.levelName, style: tt.bodyMedium?.copyWith(color: AppColors.onSurface)),
                        Text('\$${p.price.toStringAsFixed(2)}',
                            style: tt.titleSmall?.copyWith(
                                color: AppColors.primary, fontWeight: FontWeight.w700)),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          )],
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
    final tt = Theme.of(context).textTheme;
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(label,
                style: tt.bodySmall?.copyWith(color: AppColors.onSurfaceVariant)),
          ),
          Expanded(child: Text(value, style: tt.bodyMedium?.copyWith(color: AppColors.onSurface))),
        ],
      ),
    );
  }
}
