import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/theme/app_theme.dart';
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
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Competitor Analysis',
            style: Theme.of(context).textTheme.headlineMedium
                ?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.date_range_rounded,
                color: AppColors.onSurface),
            tooltip: 'Filter Date Range',
            onPressed: _pickDateRange,
          ),
        ],
      ),
      body: analysisAsync.when(
        data: (items) {
          if (items.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Container(
                    width: 64, height: 64,
                    decoration: BoxDecoration(
                      color: AppColors.surfaceContainerHigh,
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: const Icon(Icons.analytics_outlined,
                        size: 28, color: AppColors.onSurfaceVariant),
                  ),
                  const SizedBox(height: 12),
                  const Text('No competitor data available.',
                      style: TextStyle(color: AppColors.onSurfaceVariant)),
                ],
              ),
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
        loading: () => const Center(
            child: CircularProgressIndicator(color: AppColors.primary)),
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
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withOpacity(0.05),
            blurRadius: 6, offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(item.competitor,
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w800,
                      color: AppColors.onSurface)),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: AppColors.surfaceContainerHigh,
                  borderRadius: BorderRadius.circular(100),
                ),
                child: Text('${item.totalObservations} total',
                    style: const TextStyle(
                        fontSize: 11,
                        color: AppColors.onSurfaceVariant,
                        fontWeight: FontWeight.w600)),
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
    );
  }
}

class _TypeChip extends StatelessWidget {
  const _TypeChip({required this.type});
  final AnalysisType type;

  Color _typeColor() {
    return switch (type.type) {
      'sales' => AppColors.primaryContainer,
      'posm' => AppColors.primary,
      'pricing' => AppColors.secondary,
      'display' => AppColors.tertiary,
      'promotion' => AppColors.primaryContainer,
      'stock_level' => AppColors.tertiary,
      _ => AppColors.outline,
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
                style: const TextStyle(
                    fontSize: 11, color: AppColors.onSurfaceVariant)),
          if (type.totalQuantity != null)
            Text('Qty: ${type.totalQuantity}',
                style: const TextStyle(
                    fontSize: 11, color: AppColors.onSurfaceVariant)),
        ],
      ),
    );
  }
}
