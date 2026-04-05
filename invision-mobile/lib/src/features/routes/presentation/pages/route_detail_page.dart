import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/enums/route_status.dart';
import '../../data/models/route_plan_model.dart';
import '../providers/route_providers.dart';

class RouteDetailPage extends ConsumerWidget {
  const RouteDetailPage({required this.routeId, super.key});

  final int routeId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final planAsync = ref.watch(routePlanDetailProvider(routeId));

    return Scaffold(
      appBar: AppBar(title: const Text('Route Plan')),
      body: planAsync.when(
        data: (plan) => _RouteDetailBody(plan: plan),
        loading: () => const Center(child: CircularProgressIndicator()),
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
      RouteStatus.draft => Colors.grey,
      RouteStatus.published => Colors.blue,
      RouteStatus.inProgress => Colors.orange,
      RouteStatus.completed => Colors.green,
      RouteStatus.cancelled => Colors.red,
    };
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Header
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(plan.name,
                          style: theme.textTheme.headlineSmall),
                    ),
                    Chip(
                      label: Text(plan.status.name.toUpperCase()),
                      backgroundColor:
                          _statusColor(plan.status).withValues(alpha: 0.1),
                      labelStyle: TextStyle(
                        color: _statusColor(plan.status),
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
                if (plan.description != null) ...[
                  const SizedBox(height: 8),
                  Text(plan.description!,
                      style: theme.textTheme.bodyMedium),
                ],
              ],
            ),
          ),
        ),
        const SizedBox(height: 12),

        // Info
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Details', style: theme.textTheme.titleMedium),
                const Divider(),
                _InfoRow('Frequency', plan.frequency),
                _InfoRow('Start Date', plan.startDate),
                if (plan.endDate != null)
                  _InfoRow('End Date', plan.endDate!),
                if (plan.assignedUserName != null)
                  _InfoRow('Assigned To', plan.assignedUserName!),
                _InfoRow('Total Stores', '${plan.totalStores}'),
              ],
            ),
          ),
        ),

        // Store Sequence
        if (plan.stores.isNotEmpty) ...[
          const SizedBox(height: 12),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Store Sequence', style: theme.textTheme.titleMedium),
                  const Divider(),
                  ...plan.stores.map(
                    (rs) => ListTile(
                      dense: true,
                      contentPadding: EdgeInsets.zero,
                      leading: CircleAvatar(
                        radius: 14,
                        child: Text(
                          '${rs.visitOrder}',
                          style: const TextStyle(fontSize: 12),
                        ),
                      ),
                      title: Text(rs.store?.name ?? 'Store #${rs.storeId}'),
                      subtitle: rs.expectedDurationMinutes != null
                          ? Text('~${rs.expectedDurationMinutes} min')
                          : null,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
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
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          Text('$label: ',
              style: const TextStyle(fontWeight: FontWeight.w500)),
          Expanded(child: Text(value)),
        ],
      ),
    );
  }
}
