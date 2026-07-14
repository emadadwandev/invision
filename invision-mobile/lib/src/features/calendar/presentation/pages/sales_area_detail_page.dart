import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/theme/app_theme.dart';
import '../providers/calendar_providers.dart';

class SalesAreaDetailPage extends ConsumerWidget {
  const SalesAreaDetailPage({super.key, required this.areaId});
  final int areaId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final areaAsync = ref.watch(salesAreaDetailProvider(areaId));

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Sales Area',
            style: Theme.of(context).textTheme.headlineMedium
                ?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: areaAsync.when(
        loading: () => const Center(
            child: CircularProgressIndicator(color: AppColors.primary)),
        error: (e, _) => Center(child: Text('Error: $e')),
        data: (area) => SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: AppColors.surfaceContainerLowest,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.primary.withOpacity(0.06),
                      blurRadius: 8, offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(area.name,
                        style: Theme.of(context).textTheme.headlineSmall
                            ?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w700)),
                    if (area.description != null) ...[
                      const SizedBox(height: 8),
                      Text(area.description!,
                          style: const TextStyle(color: AppColors.onSurfaceVariant)),
                    ],
                    const Padding(
                        padding: EdgeInsets.symmetric(vertical: 14),
                        child: Divider(color: AppColors.outlineVariant, height: 1)),
                    _InfoRow(label: 'Status',
                        value: area.isActive ? 'Active' : 'Inactive'),
                    if (area.managerName != null)
                      _InfoRow(label: 'Manager', value: area.managerName!),
                    _InfoRow(label: 'Stores', value: '${area.storeCount}'),
                    _InfoRow(label: 'Sub-areas', value: '${area.children.length}'),
                  ],
                ),
              ),
              if (area.children.isNotEmpty) ...[
                const SizedBox(height: 16),
                Text('Sub-areas',
                    style: Theme.of(context).textTheme.titleMedium
                        ?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w700)),
                const SizedBox(height: 10),
                ...area.children.map((child) => Container(
                      margin: const EdgeInsets.only(bottom: 8),
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: AppColors.surfaceContainerLowest,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                            color: AppColors.outlineVariant.withOpacity(0.5)),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.subdirectory_arrow_right_rounded,
                              size: 18, color: AppColors.outline),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(child.name,
                                    style: const TextStyle(
                                        fontWeight: FontWeight.w700,
                                        color: AppColors.onSurface)),
                                if (child.managerName != null)
                                  Text('Manager: ${child.managerName}',
                                      style: const TextStyle(
                                          fontSize: 12,
                                          color: AppColors.onSurfaceVariant)),
                              ],
                            ),
                          ),
                          Text('${child.storeCount} stores',
                              style: const TextStyle(
                                  fontSize: 12, color: AppColors.outline)),
                        ],
                      ),
                    )),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow({required this.label, required this.value});
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
                style: const TextStyle(fontWeight: FontWeight.w600)),
          ),
          Expanded(child: Text(value)),
        ],
      ),
    );
  }
}
