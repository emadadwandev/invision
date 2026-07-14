import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/theme/app_theme.dart';
import '../../data/models/report_models.dart';

class ReportsListPage extends StatelessWidget {
  const ReportsListPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Reports',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
        actions: [
          GestureDetector(
            onTap: () => context.push('/reports/builder'),
            child: Container(
              margin: const EdgeInsets.only(right: 12),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: AppColors.surfaceContainerLow,
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.build_outlined, size: 14, color: AppColors.primary),
                  SizedBox(width: 4),
                  Text('Builder',
                      style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.w600, fontSize: 12)),
                ],
              ),
            ),
          ),
        ],
      ),
      body: ListView.separated(
        padding: const EdgeInsets.all(16),
        itemCount: FixedReportType.values.length,
        separatorBuilder: (_, _) => const SizedBox(height: 12),
        itemBuilder: (context, index) {
          final report = FixedReportType.values[index];
          return _ReportCard(report: report);
        },
      ),
    );
  }
}

class _ReportCard extends StatelessWidget {
  const _ReportCard({required this.report});
  final FixedReportType report;

  @override
  Widget build(BuildContext context) {
    final colors = [
      Colors.blue, Colors.green, Colors.purple,
      Colors.orange, Colors.amber, Colors.red,
    ];
    final color = colors[report.index % colors.length];

    return GestureDetector(
      onTap: () => context.push('/reports/${report.slug}'),
      child: Container(
        padding: const EdgeInsets.all(16),
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
              child: Icon(_iconForReport(report), color: AppColors.primary, size: 22),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(report.label,
                      style: const TextStyle(
                          fontWeight: FontWeight.w700, fontSize: 15, color: AppColors.onSurface)),
                  const SizedBox(height: 3),
                  Text(report.description,
                      style: const TextStyle(fontSize: 12, color: AppColors.onSurfaceVariant)),
                ],
              ),
            ),
            const Icon(Icons.chevron_right_rounded, color: AppColors.outline, size: 20),
          ],
        ),
      ),
    );
  }

  IconData _iconForReport(FixedReportType type) {
    return switch (type) {
      FixedReportType.sellThrough => Icons.trending_up,
      FixedReportType.sellOut => Icons.bar_chart,
      FixedReportType.sellIn => Icons.shopping_cart,
      FixedReportType.stockMovement => Icons.swap_horiz,
      FixedReportType.vendorRanking => Icons.star_outline,
      FixedReportType.salesRepPerformance => Icons.person_outline,
    };
  }
}
