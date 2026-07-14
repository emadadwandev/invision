import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/theme/app_theme.dart';
import '../../data/models/competitor_models.dart';
import '../providers/competitor_providers.dart';

class CompetitorDetailPage extends ConsumerWidget {
  const CompetitorDetailPage({super.key, required this.competitorId});
  final int competitorId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final detailAsync = ref.watch(competitorDetailProvider(competitorId));
    final productsAsync = ref.watch(competitorProductsProvider(competitorId));

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Competitor Detail',
            style: Theme.of(context).textTheme.headlineMedium
                ?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: detailAsync.when(
        data: (competitor) => RefreshIndicator(
          onRefresh: () async {
            ref.invalidate(competitorDetailProvider(competitorId));
            ref.invalidate(competitorProductsProvider(competitorId));
          },
          child: ListView(
            padding: const EdgeInsets.all(16),
            children: [
              _Header(competitor: competitor),
              const SizedBox(height: 16),
              _StatsRow(competitor: competitor),
              if (competitor.description != null) ...[
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.surfaceContainerLowest,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(
                        color: AppColors.outlineVariant.withOpacity(0.5)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Description',
                          style: TextStyle(
                              fontSize: 11,
                              color: AppColors.onSurfaceVariant,
                              fontWeight: FontWeight.w700)),
                      const SizedBox(height: 6),
                      Text(competitor.description!,
                          style: const TextStyle(color: AppColors.onSurface)),
                    ],
                  ),
                ),
              ],
              const SizedBox(height: 16),
              Text('Products',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      color: AppColors.onSurface,
                      fontWeight: FontWeight.w700)),
              const SizedBox(height: 8),
              productsAsync.when(
                data: (products) => products.isEmpty
                    ? Container(
                        padding: const EdgeInsets.all(24),
                        decoration: BoxDecoration(
                          color: AppColors.surfaceContainerLowest,
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: const Center(
                            child: Text('No products',
                                style: TextStyle(
                                    color: AppColors.onSurfaceVariant))),
                      )
                    : Column(
                        children: products
                            .map((p) => _ProductTile(product: p))
                            .toList(),
                      ),
                loading: () => const Center(
                    child: CircularProgressIndicator(
                        color: AppColors.primary)),
                error: (e, _) => Text('Error: $e'),
              ),
            ],
          ),
        ),
        loading: () => const Center(
            child: CircularProgressIndicator(color: AppColors.primary)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}

class _Header extends StatelessWidget {
  const _Header({required this.competitor});
  final Competitor competitor;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          width: 56, height: 56,
          decoration: BoxDecoration(
            color: competitor.isActive
                ? AppColors.secondary.withOpacity(0.12)
                : AppColors.errorContainer,
            borderRadius: BorderRadius.circular(16),
          ),
          child: Center(
            child: Text(
              competitor.name.substring(0, 1).toUpperCase(),
              style: TextStyle(
                fontSize: 24, fontWeight: FontWeight.w900,
                color: competitor.isActive
                    ? AppColors.secondary
                    : AppColors.error,
              ),
            ),
          ),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(competitor.name,
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                      color: AppColors.onSurface, fontWeight: FontWeight.w700)),
              const SizedBox(height: 4),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: competitor.isActive
                      ? AppColors.secondaryContainer
                      : AppColors.errorContainer,
                  borderRadius: BorderRadius.circular(100),
                ),
                child: Text(
                  competitor.isActive ? 'Active' : 'Inactive',
                  style: TextStyle(
                      fontSize: 11, fontWeight: FontWeight.w700,
                      color: competitor.isActive
                          ? AppColors.secondary
                          : AppColors.error),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _StatsRow extends StatelessWidget {
  const _StatsRow({required this.competitor});
  final Competitor competitor;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppColors.surfaceContainerLowest,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(
                  color: AppColors.outlineVariant.withOpacity(0.5)),
            ),
            child: Column(
              children: [
                Text('${competitor.productsCount ?? 0}',
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                        color: AppColors.primary, fontWeight: FontWeight.w800)),
                const Text('Products',
                    style: TextStyle(
                        color: AppColors.onSurfaceVariant, fontSize: 12)),
              ],
            ),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppColors.surfaceContainerLowest,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(
                  color: AppColors.outlineVariant.withOpacity(0.5)),
            ),
            child: Column(
              children: [
                Text('${competitor.observationsCount ?? 0}',
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                        color: AppColors.primaryContainer,
                        fontWeight: FontWeight.w800)),
                const Text('Observations',
                    style: TextStyle(
                        color: AppColors.onSurfaceVariant, fontSize: 12)),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

class _ProductTile extends StatelessWidget {
  const _ProductTile({required this.product});
  final CompetitorProduct product;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 6),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
      ),
      child: Row(
        children: [
          Container(
            width: 32, height: 32,
            decoration: BoxDecoration(
              color: AppColors.surfaceContainerHigh,
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Icon(Icons.inventory_2_outlined, size: 16,
                color: AppColors.onSurfaceVariant),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(product.name,
                    style: const TextStyle(
                        fontWeight: FontWeight.w700,
                        color: AppColors.onSurface)),
                if (product.sku != null || product.category != null)
                  Text(
                    [
                      if (product.sku != null) 'SKU: ${product.sku}',
                      if (product.category != null) product.category,
                    ].join(' · '),
                    style: const TextStyle(
                        fontSize: 11, color: AppColors.onSurfaceVariant),
                  ),
              ],
            ),
          ),
          Icon(
            Icons.circle,
            size: 10,
            color: product.isActive ? AppColors.secondary : AppColors.error,
          ),
        ],
      ),
    );
  }
}
