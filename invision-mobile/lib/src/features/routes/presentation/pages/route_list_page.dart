import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/enums/route_status.dart';
import '../../../../core/theme/app_theme.dart';
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
    final tt = Theme.of(context).textTheme;
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Route Plans', style: tt.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchController,
                    onSubmitted: (_) => _onSearch(),
                    decoration: const InputDecoration(
                      hintText: 'Search routes...',
                      prefixIcon: Icon(Icons.search_rounded, color: AppColors.outline, size: 20),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                GestureDetector(
                  onTap: _onSearch,
                  child: Container(
                    width: 48, height: 48,
                    decoration: BoxDecoration(
                      color: AppColors.primary,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.search_rounded, color: Colors.white, size: 22),
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: routesAsync.when(
              data: (routes) => routes.isEmpty
                  ? Center(
                      child: Column(mainAxisSize: MainAxisSize.min, children: [
                        Container(
                          width: 64, height: 64,
                          decoration: BoxDecoration(
                            color: AppColors.surfaceContainerHigh,
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: const Icon(Icons.route_rounded, size: 32, color: AppColors.outline),
                        ),
                        const SizedBox(height: 12),
                        Text('No route plans found.',
                            style: tt.bodyLarge?.copyWith(color: AppColors.onSurfaceVariant)),
                      ]),
                    )
                  : RefreshIndicator(
                      color: AppColors.primary,
                      onRefresh: () async => ref.invalidate(routePlansProvider(_filter)),
                      child: ListView.builder(
                        itemCount: routes.length,
                        padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
                        itemBuilder: (context, index) => _RoutePlanCard(plan: routes[index]),
                      ),
                    ),
              loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
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
      RouteStatus.inProgress => AppColors.tertiaryContainer.withOpacity(0.25),
      RouteStatus.completed => AppColors.secondaryContainer,
      RouteStatus.cancelled => AppColors.errorContainer,
    };
  }

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;
    return GestureDetector(
      onTap: () => context.push('/routes/${plan.id}'),
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surfaceContainerLowest,
          borderRadius: BorderRadius.circular(14),
          border: Border(
            left: BorderSide(color: _statusColor(plan.status), width: 3),
          ),
        ),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(plan.name,
                      style: tt.titleSmall?.copyWith(
                          color: AppColors.onSurface, fontWeight: FontWeight.w700)),
                  const SizedBox(height: 4),
                  Text('${plan.frequency}  ·  ${plan.totalStores} stores',
                      style: tt.bodySmall?.copyWith(color: AppColors.onSurfaceVariant)),
                  if (plan.assignedUserName != null)
                    Padding(
                      padding: const EdgeInsets.only(top: 2),
                      child: Text(plan.assignedUserName!,
                          style: tt.bodySmall?.copyWith(color: AppColors.primary)),
                    ),
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: _statusBg(plan.status),
                borderRadius: BorderRadius.circular(100),
              ),
              child: Text(
                plan.status.name.toUpperCase(),
                style: TextStyle(
                    color: _statusColor(plan.status),
                    fontSize: 10, fontWeight: FontWeight.w700),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
