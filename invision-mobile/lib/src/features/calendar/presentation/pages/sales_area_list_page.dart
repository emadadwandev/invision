import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/theme/app_theme.dart';
import '../../data/models/calendar_models.dart';
import '../providers/calendar_providers.dart';

class SalesAreaListPage extends ConsumerWidget {
  const SalesAreaListPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final areasAsync = ref.watch(salesAreasHierarchyProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Sales Areas',
            style: Theme.of(context).textTheme.headlineMedium
                ?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: areasAsync.when(
        loading: () => const Center(
            child: CircularProgressIndicator(color: AppColors.primary)),
        error: (e, _) => Center(child: Text('Error: $e')),
        data: (areas) => areas.isEmpty
            ? const Center(
                child: Text('No sales areas defined',
                    style: TextStyle(color: AppColors.onSurfaceVariant)))
            : ListView.builder(
                padding: const EdgeInsets.all(12),
                itemCount: areas.length,
                itemBuilder: (context, i) =>
                    _SalesAreaCard(area: areas[i], depth: 0),
              ),
      ),
    );
  }
}

class _SalesAreaCard extends StatelessWidget {
  const _SalesAreaCard({required this.area, required this.depth});
  final SalesAreaModel area;
  final int depth;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(left: depth * 16.0),
      child: Column(
        children: [
          GestureDetector(
            onTap: () => context.push('/sales-areas/${area.id}'),
            child: Container(
              margin: const EdgeInsets.symmetric(vertical: 4),
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: AppColors.surfaceContainerLowest,
                borderRadius: BorderRadius.circular(14),
                border: Border(left: BorderSide(
                  color: area.isActive ? AppColors.primary : AppColors.outline,
                  width: 3,
                )),
              ),
              child: Row(
                children: [
                  Container(
                    width: 36, height: 36,
                    decoration: BoxDecoration(
                      color: area.isActive
                          ? AppColors.primary.withOpacity(0.1)
                          : AppColors.surfaceContainerHigh,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Icon(
                      depth == 0 ? Icons.map_rounded : Icons.subdirectory_arrow_right_rounded,
                      color: area.isActive ? AppColors.primary : AppColors.outline,
                      size: 18,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          area.name,
                          style: TextStyle(
                            fontWeight: depth == 0
                                ? FontWeight.w800
                                : FontWeight.w600,
                            color: AppColors.onSurface,
                          ),
                        ),
                        if (area.managerName != null)
                          Text('${area.managerName}',
                              style: const TextStyle(
                                  fontSize: 12,
                                  color: AppColors.onSurfaceVariant)),
                        if (area.storeCount > 0)
                          Text('${area.storeCount} store(s)',
                              style: const TextStyle(
                                  fontSize: 12, color: AppColors.outline)),
                      ],
                    ),
                  ),
                  Icon(
                    area.isActive
                        ? Icons.check_circle_rounded
                        : Icons.cancel_rounded,
                    color: area.isActive ? AppColors.secondary : AppColors.error,
                    size: 18,
                  ),
                ],
              ),
            ),
          ),
          ...area.children.map(
            (child) => _SalesAreaCard(area: child, depth: depth + 1),
          ),
        ],
      ),
    );
  }
}
