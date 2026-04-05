import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/enums/route_status.dart';
import '../../../../core/theme/app_theme.dart';
import '../../data/models/route_plan_model.dart';
import '../providers/route_providers.dart';

class RouteDetailPage extends ConsumerWidget {
  const RouteDetailPage({required this.routeId, super.key});

  final int routeId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final planAsync = ref.watch(routePlanDetailProvider(routeId));

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Route Plan',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: planAsync.when(
        data: (plan) => _RouteDetailBody(plan: plan),
        loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}

class _RouteDetailBody extends StatelessWidget {
  const _RouteDetailBody({required this.plan});

  final RoutePlan plan;

  Color _statusColor(RouteStatus status) {
    return switch (status) {
      RouteStatus.draft => AppColors.onSurfaceVariant,
      RouteStatus.published => AppColors.primaryContainer,
      RouteStatus.inProgress => AppColors.tertiary,
      RouteStatus.completed => AppColors.secondary,
      RouteStatus.cancelled => AppColors.error,
    };
  }

  Color _statusBg(RouteStatus status) {
    return switch (status) {
      RouteStatus.draft => AppColors.surfaceContainerHigh,
      RouteStatus.published => AppColors.surfaceContainerLow,
      RouteStatus.inProgress => AppColors.tertiaryContainer.withOpacity(0.3),
      RouteStatus.completed => AppColors.secondaryContainer,
      RouteStatus.cancelled => AppColors.errorContainer,
    };
  }

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Header
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: [_statusColor(plan.status), _statusColor(plan.status).withOpacity(0.7)],
              begin: Alignment.topLeft, end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(plan.name,
                        style: tt.headlineSmall?.copyWith(color: Colors.white)),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(100),
                    ),
                    child: Text(plan.status.name.toUpperCase(),
                        style: const TextStyle(
                            color: Colors.white, fontSize: 10, fontWeight: FontWeight.w700)),
                  ),
                ],
              ),
              if (plan.description != null) ...[const SizedBox(height: 8),
                Text(plan.description!,
                    style: tt.bodyMedium?.copyWith(color: Colors.white70))],
            ],
          ),
        ),
        const SizedBox(height: 14),

        // Info
        Container(
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            color: AppColors.surfaceContainerLowest,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Details',
                  style: tt.titleMedium?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w700)),
              const SizedBox(height: 10),
              const Divider(color: AppColors.outlineVariant, height: 1),
              const SizedBox(height: 10),
              _InfoRow('Frequency', plan.frequency),
              _InfoRow('Start Date', plan.startDate),
              if (plan.endDate != null) _InfoRow('End Date', plan.endDate!),
              if (plan.assignedUserName != null)
                _InfoRow('Assigned To', plan.assignedUserName!),
              _InfoRow('Total Stores', '${plan.totalStores}'),
            ],
          ),
        ),

        // Store Sequence
        if (plan.stores.isNotEmpty) ...[const SizedBox(height: 14),
          Container(
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              color: AppColors.surfaceContainerLowest,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Store Sequence',
                    style: tt.titleMedium?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w700)),
                const SizedBox(height: 10),
                const Divider(color: AppColors.outlineVariant, height: 1),
                const SizedBox(height: 10),
                ...plan.stores.map(
                  (rs) => Padding(
                    padding: const EdgeInsets.only(bottom: 8),
                    child: Row(
                      children: [
                        Container(
                          width: 28, height: 28,
                          decoration: BoxDecoration(
                            color: AppColors.surfaceContainerLow,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          alignment: Alignment.center,
                          child: Text('${rs.visitOrder}',
                              style: const TextStyle(
                                  fontSize: 12, fontWeight: FontWeight.w700,
                                  color: AppColors.primary)),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(rs.store?.name ?? 'Store #${rs.storeId}',
                              style: tt.bodyMedium?.copyWith(color: AppColors.onSurface)),
                        ),
                        if (rs.expectedDurationMinutes != null)
                          Text('~${rs.expectedDurationMinutes} min',
                              style: tt.bodySmall?.copyWith(color: AppColors.onSurfaceVariant)),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          )],
      ],
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow(this.label, this.value);

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 110,
            child: Text('$label',
                style: Theme.of(context).textTheme.bodySmall
                    ?.copyWith(color: AppColors.onSurfaceVariant)),
          ),
          Expanded(
            child: Text(value,
                style: Theme.of(context).textTheme.bodyMedium
                    ?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w600)),
          ),
        ],
      ),
    );
  }
}
