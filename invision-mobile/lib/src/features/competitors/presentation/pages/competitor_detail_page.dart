import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

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
      appBar: AppBar(title: const Text('Competitor Detail')),
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
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Description',
                            style: Theme.of(context)
                                .textTheme
                                .titleSmall
                                ?.copyWith(color: Colors.grey)),
                        const SizedBox(height: 4),
                        Text(competitor.description!),
                      ],
                    ),
                  ),
                ),
              ],
              const SizedBox(height: 16),
              Text('Products',
                  style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 8),
              productsAsync.when(
                data: (products) => products.isEmpty
                    ? const Card(
                        child: Padding(
                          padding: EdgeInsets.all(24),
                          child: Center(
                              child: Text('No products',
                                  style: TextStyle(color: Colors.grey))),
                        ),
                      )
                    : Column(
                        children: products
                            .map((p) => _ProductTile(product: p))
                            .toList(),
                      ),
                loading: () =>
                    const Center(child: CircularProgressIndicator()),
                error: (e, _) => Text('Error: $e'),
              ),
            ],
          ),
        ),
        loading: () => const Center(child: CircularProgressIndicator()),
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
        CircleAvatar(
          radius: 28,
          backgroundColor: Colors.indigo.shade100,
          child: Text(
            competitor.name.substring(0, 1).toUpperCase(),
            style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                color: Colors.indigo.shade700),
          ),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(competitor.name,
                  style: Theme.of(context).textTheme.headlineSmall),
              Chip(
                label: Text(
                  competitor.isActive ? 'Active' : 'Inactive',
                  style: TextStyle(
                    fontSize: 11,
                    color: competitor.isActive ? Colors.green : Colors.red,
                  ),
                ),
                backgroundColor: competitor.isActive
                    ? Colors.green.shade50
                    : Colors.red.shade50,
                side: BorderSide.none,
                padding: EdgeInsets.zero,
                visualDensity: VisualDensity.compact,
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
          child: Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  Text('${competitor.productsCount ?? 0}',
                      style: Theme.of(context).textTheme.headlineMedium),
                  const Text('Products',
                      style: TextStyle(color: Colors.grey, fontSize: 12)),
                ],
              ),
            ),
          ),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  Text('${competitor.observationsCount ?? 0}',
                      style: Theme.of(context).textTheme.headlineMedium),
                  const Text('Observations',
                      style: TextStyle(color: Colors.grey, fontSize: 12)),
                ],
              ),
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
    return Card(
      margin: const EdgeInsets.only(bottom: 4),
      child: ListTile(
        dense: true,
        leading: const Icon(Icons.inventory_2_outlined, size: 20),
        title: Text(product.name),
        subtitle: Text(
          [
            if (product.sku != null) 'SKU: ${product.sku}',
            if (product.category != null) product.category,
          ].join(' · '),
          style: const TextStyle(fontSize: 12),
        ),
        trailing: Icon(
          Icons.circle,
          size: 10,
          color: product.isActive ? Colors.green : Colors.red,
        ),
      ),
    );
  }
}
