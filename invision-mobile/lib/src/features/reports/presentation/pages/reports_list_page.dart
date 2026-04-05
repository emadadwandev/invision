import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../data/models/report_models.dart';

class ReportsListPage extends StatelessWidget {
  const ReportsListPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Reports'),
        actions: [
          TextButton.icon(
            onPressed: () => context.push('/reports/builder'),
            icon: const Icon(Icons.build_outlined, size: 18),
            label: const Text('Builder'),
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

    return Card(
      elevation: 1,
      child: InkWell(
        onTap: () => context.push('/reports/${report.slug}'),
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(_iconForReport(report), color: color, size: 22),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(report.label,
                        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15)),
                    const SizedBox(height: 2),
                    Text(report.description,
                        style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                  ],
                ),
              ),
              Icon(Icons.chevron_right, color: Colors.grey.shade400),
            ],
          ),
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
