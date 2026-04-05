import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../../core/theme/app_theme.dart';
import '../../data/models/report_models.dart';
import '../providers/report_providers.dart';

class ReportDetailPage extends ConsumerStatefulWidget {
  const ReportDetailPage({super.key, required this.slug});
  final String slug;

  @override
  ConsumerState<ReportDetailPage> createState() => _ReportDetailPageState();
}

class _ReportDetailPageState extends ConsumerState<ReportDetailPage> {
  DateTime? _dateFrom;
  DateTime? _dateTo;

  FixedReportFilter get _filter => FixedReportFilter(
        slug: widget.slug,
        dateFrom: _dateFrom != null ? DateFormat('yyyy-MM-dd').format(_dateFrom!) : null,
        dateTo: _dateTo != null ? DateFormat('yyyy-MM-dd').format(_dateTo!) : null,
      );

  String get _title {
    try {
      return FixedReportType.values.firstWhere((e) => e.slug == widget.slug).label;
    } catch (_) {
      return 'Report';
    }
  }

  @override
  Widget build(BuildContext context) {
    final report = ref.watch(fixedReportProvider(_filter));

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text(_title,
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
        actions: [
          PopupMenuButton<String>(
            icon: const Icon(Icons.download_rounded, size: 20, color: AppColors.primary),
            onSelected: _handleExport,
            itemBuilder: (_) => const [
              PopupMenuItem(value: 'excel', child: Text('Export Excel')),
              PopupMenuItem(value: 'pdf', child: Text('Export PDF')),
            ],
          ),
        ],
      ),
      body: Column(
        children: [
          // Date filter bar
          Container(
            color: AppColors.surfaceContainerLow,
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            child: Row(
              children: [
                Expanded(
                  child: _DateChip(
                    label: _dateFrom != null
                        ? DateFormat('MMM d').format(_dateFrom!)
                        : 'From',
                    onTap: () => _pickDate(true),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 4),
                  child: Text('–', style: TextStyle(color: AppColors.outline)),
                ),
                Expanded(
                  child: _DateChip(
                    label: _dateTo != null
                        ? DateFormat('MMM d').format(_dateTo!)
                        : 'To',
                    onTap: () => _pickDate(false),
                  ),
                ),
                const SizedBox(width: 8),
                GestureDetector(
                  onTap: () => setState(() {}),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                    decoration: BoxDecoration(
                      color: AppColors.primary,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Text('Apply',
                        style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w700)),
                  ),
                ),
                if (_dateFrom != null || _dateTo != null) ...[const SizedBox(width: 4),
                  GestureDetector(
                    onTap: () => setState(() { _dateFrom = null; _dateTo = null; }),
                    child: const Icon(Icons.close_rounded, size: 18, color: AppColors.outline),
                  )],
              ],
            ),
          ),

          // Report data
          Expanded(
            child: report.when(
              data: (data) => _ReportTable(report: data),
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, _) => Center(child: Text('Error: $e')),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _pickDate(bool isFrom) async {
    final picked = await showDatePicker(
      context: context,
      initialDate: isFrom ? (_dateFrom ?? DateTime.now()) : (_dateTo ?? DateTime.now()),
      firstDate: DateTime(2024),
      lastDate: DateTime.now(),
    );
    if (picked != null) {
      setState(() {
        if (isFrom) {
          _dateFrom = picked;
        } else {
          _dateTo = picked;
        }
      });
    }
  }

  void _handleExport(String format) {
    final repo = ref.read(reportRepositoryProvider);
    final slug = widget.slug.replaceAll('-', '_');
    final url = format == 'excel'
        ? repo.getExcelExportUrl(slug,
            dateFrom: _filter.dateFrom, dateTo: _filter.dateTo)
        : repo.getPdfExportUrl(slug,
            dateFrom: _filter.dateFrom, dateTo: _filter.dateTo);
    launchUrl(Uri.parse(url), mode: LaunchMode.externalApplication);
  }
}

class _DateChip extends StatelessWidget {
  const _DateChip({required this.label, required this.onTap});
  final String label;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  border: Border.all(color: AppColors.outlineVariant),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.calendar_today_rounded, size: 14, color: AppColors.outline),
                    const SizedBox(width: 6),
                    Text(label, style: const TextStyle(fontSize: 13, color: AppColors.onSurface)),
                  ],
                ),
              ),
    );
  }
}

class _ReportTable extends StatelessWidget {
  const _ReportTable({required this.report});
  final ReportData report;

  @override
  Widget build(BuildContext context) {
    if (report.rows.isEmpty) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 64, height: 64,
              decoration: BoxDecoration(
                  color: AppColors.surfaceContainerHigh,
                  borderRadius: BorderRadius.circular(16)),
              child: const Icon(Icons.table_chart_outlined, size: 32, color: AppColors.outline),
            ),
            const SizedBox(height: 10),
            const Text('No data found',
                style: TextStyle(color: AppColors.onSurfaceVariant)),
          ],
        ),
      );
    }

    final columns = report.columns;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
          child: Text(
            '${report.rows.length} rows  ·  ${report.period ?? ''}',
            style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
          ),
        ),
        Expanded(
          child: SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: SingleChildScrollView(
              child: DataTable(
                columnSpacing: 16,
                headingRowHeight: 40,
                dataRowMinHeight: 36,
                dataRowMaxHeight: 44,
                columns: [
                  const DataColumn(label: Text('#', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 12))),
                  ...columns.map((c) => DataColumn(
                        label: Text(
                          c.replaceAll('_', ' ').toUpperCase(),
                          style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 11),
                        ),
                      )),
                ],
                rows: List.generate(report.rows.length, (i) {
                  final row = report.rows[i];
                  return DataRow(cells: [
                    DataCell(Text('${i + 1}', style: const TextStyle(fontSize: 12, color: Colors.grey))),
                    ...columns.map((c) {
                      final val = row[c];
                      return DataCell(Text(
                        _formatValue(val),
                        style: const TextStyle(fontSize: 12),
                      ));
                    }),
                  ]);
                }),
              ),
            ),
          ),
        ),
      ],
    );
  }

  String _formatValue(dynamic val) {
    if (val == null) return '-';
    if (val is num && val > 999) {
      return NumberFormat('#,##0.##').format(val);
    }
    return val.toString();
  }
}
