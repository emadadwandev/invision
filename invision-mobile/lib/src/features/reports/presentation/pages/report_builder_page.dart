import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../../core/theme/app_theme.dart';
import '../../data/models/report_models.dart';
import '../providers/report_providers.dart';

class ReportBuilderPage extends ConsumerStatefulWidget {
  const ReportBuilderPage({super.key});

  @override
  ConsumerState<ReportBuilderPage> createState() => _ReportBuilderPageState();
}

class _ReportBuilderPageState extends ConsumerState<ReportBuilderPage> {
  String _entity = 'sales_orders';
  String? _groupBy;
  String _orderDir = 'desc';
  int _limit = 100;
  bool _hasRun = false;

  DynamicReportFilter get _filter => DynamicReportFilter(
        entity: _entity,
        groupBy: _groupBy,
        orderDir: _orderDir,
        limit: _limit,
      );

  @override
  Widget build(BuildContext context) {
    final entities = ref.watch(reportEntitiesProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Report Builder',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: entities.when(
        data: (entityList) => _buildContent(entityList),
        loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
        error: (e, _) => Center(child: Text('Error loading entities: $e')),
      ),
    );
  }

  Widget _buildContent(List<ReportEntity> entityList) {
    final currentEntity = entityList.firstWhere(
      (e) => e.key == _entity,
      orElse: () => entityList.first,
    );

    return Column(
      children: [
        // Configuration panel
        Container(
          color: AppColors.surfaceContainerLow,
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Entity selector
              DropdownButtonFormField<String>(
                key: ValueKey(_entity),
                initialValue: _entity,
                decoration: const InputDecoration(
                  labelText: 'Data Source',
                  isDense: true,
                  border: OutlineInputBorder(),
                ),
                items: entityList.map((e) => DropdownMenuItem(
                      value: e.key,
                      child: Text(e.label),
                    )).toList(),
                onChanged: (v) => setState(() {
                  _entity = v ?? 'sales_orders';
                  _groupBy = null;
                }),
              ),
              const SizedBox(height: 10),
              Row(
                children: [
                  // Group By
                  Expanded(
                    child: DropdownButtonFormField<String>(
                      key: ValueKey('$_entity-group'),
                      initialValue: _groupBy,
                      decoration: const InputDecoration(
                        labelText: 'Group By',
                        isDense: true,
                        border: OutlineInputBorder(),
                      ),
                      items: [
                        const DropdownMenuItem(value: null, child: Text('None')),
                        ...currentEntity.groupByOptions.map((o) => DropdownMenuItem(
                              value: o,
                              child: Text(o.replaceAll('_', ' ')),
                            )),
                      ],
                      onChanged: (v) => setState(() => _groupBy = v),
                    ),
                  ),
                  const SizedBox(width: 8),
                  // Order direction
                  Expanded(
                    child: DropdownButtonFormField<String>(
                      key: const ValueKey('order-dir'),
                      initialValue: _orderDir,
                      decoration: const InputDecoration(
                        labelText: 'Direction',
                        isDense: true,
                        border: OutlineInputBorder(),
                      ),
                      items: const [
                        DropdownMenuItem(value: 'desc', child: Text('Desc')),
                        DropdownMenuItem(value: 'asc', child: Text('Asc')),
                      ],
                      onChanged: (v) => setState(() => _orderDir = v ?? 'desc'),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              Row(
                children: [
                  // Limit
                  SizedBox(
                    width: 120,
                    child: TextFormField(
                      initialValue: '$_limit',
                      decoration: const InputDecoration(
                        labelText: 'Limit',
                        isDense: true,
                        border: OutlineInputBorder(),
                      ),
                      keyboardType: TextInputType.number,
                      onChanged: (v) => _limit = int.tryParse(v) ?? 100,
                    ),
                  ),
                  const SizedBox(width: 12),
                  GestureDetector(
                    onTap: () => setState(() => _hasRun = true),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [AppColors.primary, AppColors.primaryContainer],
                        ),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.play_arrow_rounded, size: 16, color: Colors.white),
                          SizedBox(width: 6),
                          Text('Run Report',
                              style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 13)),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),

        // Results
        if (_hasRun)
          Expanded(
            child: Consumer(
              builder: (context, ref, _) {
                final report = ref.watch(dynamicReportProvider(_filter));
                return report.when(
                  data: (data) => _ResultsTable(report: data),
                  loading: () => const Center(child: CircularProgressIndicator()),
                  error: (e, _) => Center(child: Text('Error: $e')),
                );
              },
            ),
          ),
      ],
    );
  }
}

class _ResultsTable extends StatelessWidget {
  const _ResultsTable({required this.report});
  final ReportData report;

  @override
  Widget build(BuildContext context) {
    if (report.rows.isEmpty) {
      return const Center(child: Text('No data found'));
    }

    final columns = report.columns;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
          child: Text(
            '${report.rows.length} rows  ·  ${report.generated}',
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
                      final display = val == null
                          ? '-'
                          : (val is num && val > 999)
                              ? NumberFormat('#,##0.##').format(val)
                              : val.toString();
                      return DataCell(Text(display, style: const TextStyle(fontSize: 12)));
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
}
