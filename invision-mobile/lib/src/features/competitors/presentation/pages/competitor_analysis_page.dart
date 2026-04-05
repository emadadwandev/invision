import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../data/models/competitor_models.dart';
import '../providers/competitor_providers.dart';

class CompetitorAnalysisPage extends ConsumerStatefulWidget {
  const CompetitorAnalysisPage({super.key});

  @override
  ConsumerState<CompetitorAnalysisPage> createState() =>
      _CompetitorAnalysisPageState();
}

class _CompetitorAnalysisPageState
    extends ConsumerState<CompetitorAnalysisPage> {
  AnalysisFilter _filter = const AnalysisFilter();

  Future<void> _pickDateRange() async {
    final picked = await showDateRangePicker(
      context: context,
      firstDate: DateTime(2024),
      lastDate: DateTime.now(),
    );
    if (picked != null) {
      setState(() {
        _filter = AnalysisFilter(
          storeId: _filter.storeId,
          from: picked.start.toIso8601String().split('T').first,
          to: picked.end.toIso8601String().split('T').first,
        );
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final analysisAsync = ref.watch(competitorAnalysisProvider(_filter));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Competitor Analysis'),
        actions: [
          IconButton(
            icon: const Icon(Icons.date_range),
            tooltip: 'Filter Date Range',
            onPressed: _pickDateRange,
          ),
        ],
      ),
      body: analysisAsync.when(
        data: (items) {
          if (items.isEmpty) {
            return const Center(
              child: Text('No competitor data available.',
                  style: TextStyle(color: Colors.grey)),
            );
          }
          return RefreshIndicator(
            onRefresh: () async =>
                ref.invalidate(competitorAnalysisProvider(_filter)),
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: items.length,
              itemBuilder: (context, index) =>
                  _AnalysisCard(item: items[index]),
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}

class _AnalysisCard extends StatelessWidget {
  const _AnalysisCard({required this.item});
  final CompetitorAnalysisItem item;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(item.competitor,
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold)),
                Chip(
                  label: Text('${item.totalObservations} total',
                      style: const TextStyle(fontSize: 11)),
                  side: BorderSide.none,
                  padding: EdgeInsets.zero,
                  visualDensity: VisualDensity.compact,
                ),
              ],
            ),
            const SizedBox(height: 12),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: item.types.map((t) => _TypeChip(type: t)).toList(),
            ),
          ],
        ),
      ),
    );
  }
}

class _TypeChip extends StatelessWidget {
  const _TypeChip({required this.type});
  final AnalysisType type;

  Color _typeColor() {
    return switch (type.type) {
      'sales' => Colors.blue,
      'posm' => Colors.purple,
      'pricing' => Colors.green,
      'display' => Colors.amber,
      'promotion' => Colors.indigo,
      'stock_level' => Colors.orange,
      _ => Colors.grey,
    };
  }

  @override
  Widget build(BuildContext context) {
    final color = _typeColor();
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(type.type.toUpperCase(),
              style: TextStyle(
                  fontSize: 10,
                  fontWeight: FontWeight.bold,
                  color: color)),
          Text('${type.count} observations',
              style: const TextStyle(fontSize: 12)),
          if (type.avgPrice != null)
            Text('Avg: \$${type.avgPrice!.toStringAsFixed(2)}',
                style: const TextStyle(fontSize: 11, color: Colors.grey)),
          if (type.totalQuantity != null)
            Text('Qty: ${type.totalQuantity}',
                style: const TextStyle(fontSize: 11, color: Colors.grey)),
        ],
      ),
    );
  }
}
