import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/theme/app_theme.dart';
import '../../data/models/inquiry_models.dart';
import '../providers/dashboard_providers.dart';

class RouteInquiryPage extends ConsumerStatefulWidget {
  const RouteInquiryPage({super.key});

  @override
  ConsumerState<RouteInquiryPage> createState() => _RouteInquiryPageState();
}

class _RouteInquiryPageState extends ConsumerState<RouteInquiryPage> {
  String? _status;

  RouteInquiryFilter get _filter => RouteInquiryFilter(status: _status);

  @override
  Widget build(BuildContext context) {
    final routes = ref.watch(routeInquiryProvider(_filter));

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Route Inquiry',
            style: Theme.of(context).textTheme.headlineMedium
                ?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: Column(
        children: [
          Container(
            color: AppColors.surfaceContainerLow,
            padding: const EdgeInsets.all(12),
            child: DropdownButtonFormField<String>(
              key: ValueKey(_status),
              initialValue: _status,
              decoration: const InputDecoration(labelText: 'Status', isDense: true),
              items: const [
                DropdownMenuItem(value: null, child: Text('All')),
                DropdownMenuItem(value: 'pending', child: Text('Pending')),
                DropdownMenuItem(value: 'in_progress', child: Text('In Progress')),
                DropdownMenuItem(value: 'completed', child: Text('Completed')),
                DropdownMenuItem(value: 'cancelled', child: Text('Cancelled')),
              ],
              onChanged: (v) => setState(() => _status = v),
            ),
          ),
          Expanded(
            child: routes.when(
              data: (list) => _RouteList(routes: list),
              loading: () => const Center(
                  child: CircularProgressIndicator(color: AppColors.primary)),
              error: (e, _) => Center(child: Text('Error: $e')),
            ),
          ),
        ],
      ),
    );
  }
}

class _RouteList extends StatelessWidget {
  const _RouteList({required this.routes});
  final List<RouteInquiryItem> routes;

  @override
  Widget build(BuildContext context) {
    if (routes.isEmpty) {
      return const Center(child: Text('No route instances found.'));
    }
    return ListView.separated(
      padding: const EdgeInsets.all(12),
      itemCount: routes.length,
      separatorBuilder: (_, _) => const SizedBox(height: 8),
      itemBuilder: (_, i) {
        final r = routes[i];
        final statusColor = switch (r.status) {
          'completed' => AppColors.secondary,
          'in_progress' => AppColors.primaryContainer,
          'cancelled' => AppColors.error,
          _ => AppColors.outline,
        };
        final statusBg = switch (r.status) {
          'completed' => AppColors.secondaryContainer,
          'in_progress' => AppColors.surfaceContainerLow,
          'cancelled' => AppColors.errorContainer,
          _ => AppColors.surfaceContainerHigh,
        };
        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: AppColors.surfaceContainerLowest,
            borderRadius: BorderRadius.circular(14),
            border: Border(left: BorderSide(color: statusColor, width: 3)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(child: Text(r.routeName ?? '-',
                      style: const TextStyle(
                          fontWeight: FontWeight.w700,
                          color: AppColors.onSurface, fontSize: 14))),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: statusBg, borderRadius: BorderRadius.circular(100)),
                    child: Text(r.status.replaceAll('_', ' ').toUpperCase(),
                        style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700,
                            color: statusColor)),
                  ),
                ],
              ),
              const SizedBox(height: 4),
              Text('${r.user ?? '-'}  ·  ${r.routeDate}',
                  style: const TextStyle(fontSize: 12, color: AppColors.onSurfaceVariant)),
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(4),
                      child: LinearProgressIndicator(
                        value: (r.completionPct / 100).clamp(0.0, 1.0),
                        backgroundColor: AppColors.surfaceContainerHigh,
                        valueColor: const AlwaysStoppedAnimation(AppColors.primary),
                        minHeight: 8,
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text('${r.completionPct.toStringAsFixed(0)}%',
                      style: const TextStyle(
                          fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.onSurface)),
                ],
              ),
              const SizedBox(height: 6),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('${r.completedVisits}/${r.totalVisits} visits',
                      style: const TextStyle(
                          fontSize: 11, color: AppColors.onSurfaceVariant)),
                  if (r.distanceKm != null)
                    Text('${r.distanceKm!.toStringAsFixed(1)} km',
                        style: const TextStyle(
                            fontSize: 11, color: AppColors.onSurfaceVariant)),
                ],
              ),
            ],
          ),
        );
      },
    );
  }
}
