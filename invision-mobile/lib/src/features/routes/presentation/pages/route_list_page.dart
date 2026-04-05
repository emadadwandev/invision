import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/enums/route_status.dart';
import '../../data/models/route_plan_model.dart';
import '../providers/route_providers.dart';

class RouteListPage extends ConsumerStatefulWidget {
  const RouteListPage({super.key});

  @override
  ConsumerState<RouteListPage> createState() => _RouteListPageState();
}

class _RouteListPageState extends ConsumerState<RouteListPage> {
  final _searchController = TextEditingController();
  RouteFilter _filter = const RouteFilter();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _onSearch() {
    setState(() {
      _filter = _filter.copyWith(search: _searchController.text);
    });
  }

  @override
  Widget build(BuildContext context) {
    final routesAsync = ref.watch(routePlansProvider(_filter));

    return Scaffold(
      appBar: AppBar(title: const Text('Route Plans')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchController,
                    decoration: const InputDecoration(
                      hintText: 'Search routes...',
                      prefixIcon: Icon(Icons.search),
                      border: OutlineInputBorder(),
                      isDense: true,
                    ),
                    onSubmitted: (_) => _onSearch(),
                  ),
                ),
                const SizedBox(width: 8),
                FilledButton(
                  onPressed: _onSearch,
                  child: const Text('Search'),
                ),
              ],
            ),
          ),
          Expanded(
            child: routesAsync.when(
              data: (routes) => routes.isEmpty
                  ? const Center(child: Text('No route plans found.'))
                  : RefreshIndicator(
                      onRefresh: () async =>
                          ref.invalidate(routePlansProvider(_filter)),
                      child: ListView.builder(
                        itemCount: routes.length,
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        itemBuilder: (context, index) =>
                            _RoutePlanCard(plan: routes[index]),
                      ),
                    ),
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, _) => Center(child: Text('Error: $e')),
            ),
          ),
        ],
      ),
    );
  }
}

class _RoutePlanCard extends StatelessWidget {
  const _RoutePlanCard({required this.plan});

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

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        title: Text(plan.name, style: theme.textTheme.titleSmall),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              '${plan.frequency} · ${plan.totalStores} stores',
              style: theme.textTheme.bodySmall,
            ),
            if (plan.assignedUserName != null)
              Text(
                plan.assignedUserName!,
                style: theme.textTheme.bodySmall?.copyWith(
                  color: theme.colorScheme.primary,
                ),
              ),
          ],
        ),
        trailing: Chip(
          label: Text(
            plan.status.name.toUpperCase(),
            style: TextStyle(
              color: _statusColor(plan.status),
              fontSize: 10,
              fontWeight: FontWeight.bold,
            ),
          ),
          backgroundColor: _statusColor(plan.status).withValues(alpha: 0.1),
          side: BorderSide.none,
          padding: EdgeInsets.zero,
          visualDensity: VisualDensity.compact,
        ),
        onTap: () => context.push('/routes/${plan.id}'),
      ),
    );
  }
}
