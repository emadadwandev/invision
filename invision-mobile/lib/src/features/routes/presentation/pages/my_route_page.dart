import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/enums/route_status.dart';
import '../../../gps_tracking/presentation/providers/gps_tracking_providers.dart';
import '../../../../core/enums/visit_status.dart';
import '../../../../core/theme/app_theme.dart';
import '../../data/models/route_instance_model.dart';
import '../providers/route_providers.dart';

class MyRoutePage extends ConsumerWidget {
  const MyRoutePage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final todayAsync = ref.watch(myRouteTodayProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text("Today's Route",
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: todayAsync.when(
        data: (instance) => instance == null
            ? Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      width: 72, height: 72,
                      decoration: BoxDecoration(
                        color: AppColors.surfaceContainerHigh,
                        borderRadius: BorderRadius.circular(18),
                      ),
                      child: const Icon(Icons.route_rounded, size: 36, color: AppColors.outline),
                    ),
                    const SizedBox(height: 14),
                    Text('No route assigned for today.',
                        style: Theme.of(context).textTheme.bodyLarge
                            ?.copyWith(color: AppColors.onSurfaceVariant)),
                  ],
                ),
              )
            : _MyRouteBody(instance: instance),
        loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}

class _MyRouteBody extends ConsumerStatefulWidget {
  const _MyRouteBody({required this.instance});

  final RouteInstance instance;

  @override
  ConsumerState<_MyRouteBody> createState() => _MyRouteBodyState();
}

class _MyRouteBodyState extends ConsumerState<_MyRouteBody> {
  bool _loading = false;

  Future<void> _startRoute() async {
    setState(() => _loading = true);
    try {
      final repo = ref.read(routeRepositoryProvider);
      await repo.startRouteInstance(widget.instance.id);
      ref.invalidate(myRouteTodayProvider);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to start route: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _checkIn(StoreVisit visit) async {
    setState(() => _loading = true);
    try {
      final locationService = ref.read(locationServiceProvider);
      final position = await locationService.getCurrentPosition();

      if (position == null) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('GPS unavailable. Please enable location services and try again.')),
          );
        }
        return;
      }

      final repo = ref.read(routeRepositoryProvider);
      await repo.checkIn(
        visit.id,
        latitude: position.latitude,
        longitude: position.longitude,
      );
      ref.invalidate(myRouteTodayProvider);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Check-in failed: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _checkOut(StoreVisit visit) async {
    setState(() => _loading = true);
    try {
      final locationService = ref.read(locationServiceProvider);
      final position = await locationService.getCurrentPosition();

      if (position == null) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('GPS unavailable. Please enable location services and try again.')),
          );
        }
        return;
      }

      final repo = ref.read(routeRepositoryProvider);
      await repo.checkOut(
        visit.id,
        latitude: position.latitude,
        longitude: position.longitude,
      );
      ref.invalidate(myRouteTodayProvider);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Check-out failed: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _skipVisit(StoreVisit visit) async {
    final reason = await showDialog<String>(
      context: context,
      builder: (ctx) {
        final controller = TextEditingController();
        return AlertDialog(
          title: const Text('Skip Visit'),
          content: TextField(
            controller: controller,
            decoration: const InputDecoration(
              labelText: 'Reason',
              border: OutlineInputBorder(),
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Cancel'),
            ),
            FilledButton(
              onPressed: () => Navigator.pop(ctx, controller.text),
              child: const Text('Skip'),
            ),
          ],
        );
      },
    );

    if (reason == null || reason.isEmpty) return;

    setState(() => _loading = true);
    try {
      final repo = ref.read(routeRepositoryProvider);
      await repo.skipVisit(visit.id, reason: reason);
      ref.invalidate(myRouteTodayProvider);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Skip failed: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final instance = widget.instance;
    final tt = Theme.of(context).textTheme;
    final isNotStarted = instance.status == RouteStatus.published;

    return Stack(
      children: [
        ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Summary Card
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [AppColors.primary, AppColors.primaryContainer],
                  begin: Alignment.topLeft, end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    instance.routePlanName ?? 'Route #${instance.routePlanId}',
                    style: tt.titleLarge?.copyWith(color: Colors.white),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      _StatChip(
                        label: 'Progress',
                        value: '${instance.completionPercentage.toStringAsFixed(0)}%',
                      ),
                      const SizedBox(width: 8),
                      _StatChip(
                        label: 'Visits',
                        value: '${instance.completedVisits}/${instance.totalVisits}',
                      ),
                      if (instance.totalDistanceKm != null) ...[const SizedBox(width: 8),
                        _StatChip(
                          label: 'Distance',
                          value: '${instance.totalDistanceKm!.toStringAsFixed(1)} km',
                        )],
                    ],
                  ),
                  if (isNotStarted) ...[const SizedBox(height: 14),
                    GestureDetector(
                      onTap: _loading ? null : _startRoute,
                      child: Container(
                        width: double.infinity, height: 44,
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: Colors.white.withOpacity(0.5)),
                        ),
                        alignment: Alignment.center,
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.play_arrow_rounded, color: Colors.white, size: 20),
                            SizedBox(width: 6),
                            Text('Start Route',
                                style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
                          ],
                        ),
                      ),
                    )],
                ],
              ),
            ),
            const SizedBox(height: 14),

            // Visits List
            Text('Store Visits',
                style: tt.titleMedium?.copyWith(color: AppColors.onSurface)),
            const SizedBox(height: 10),
            ...instance.visits.map(
              (visit) => _VisitCard(
                visit: visit,
                onCheckIn: () => _checkIn(visit),
                onCheckOut: () => _checkOut(visit),
                onSkip: () => _skipVisit(visit),
                loading: _loading,
              ),
            ),
          ],
        ),
        if (_loading)
          const Positioned.fill(
            child: ColoredBox(
              color: Color(0x44000000),
              child: Center(child: CircularProgressIndicator(color: AppColors.primary)),
            ),
          ),
      ],
    );
  }
}

class _StatChip extends StatelessWidget {
  const _StatChip({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.15),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: Colors.white.withOpacity(0.3)),
      ),
      child: Column(
        children: [
          Text(value,
              style: const TextStyle(
                  color: Colors.white, fontWeight: FontWeight.w700, fontSize: 15)),
          const SizedBox(height: 2),
          Text(label, style: const TextStyle(color: Colors.white70, fontSize: 10)),
        ],
      ),
    );
  }
}

class _VisitCard extends StatelessWidget {
  const _VisitCard({
    required this.visit,
    required this.onCheckIn,
    required this.onCheckOut,
    required this.onSkip,
    required this.loading,
  });

  final StoreVisit visit;
  final VoidCallback onCheckIn;
  final VoidCallback onCheckOut;
  final VoidCallback onSkip;
  final bool loading;

  Color _statusColor(VisitStatus status) {
    return switch (status) {
      VisitStatus.pending => AppColors.onSurfaceVariant,
      VisitStatus.checkedIn => AppColors.tertiary,
      VisitStatus.completed => AppColors.secondary,
      VisitStatus.skipped => AppColors.error,
    };
  }

  Color _statusBg(VisitStatus status) {
    return switch (status) {
      VisitStatus.pending => AppColors.surfaceContainerHigh,
      VisitStatus.checkedIn => AppColors.tertiaryContainer.withOpacity(0.3),
      VisitStatus.completed => AppColors.secondaryContainer,
      VisitStatus.skipped => AppColors.errorContainer,
    };
  }

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest,
        borderRadius: BorderRadius.circular(14),
        border: Border(
          left: BorderSide(color: _statusColor(visit.status), width: 3),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 28, height: 28,
                decoration: BoxDecoration(
                  color: _statusBg(visit.status),
                  borderRadius: BorderRadius.circular(8),
                ),
                alignment: Alignment.center,
                child: Text('${visit.visitOrder}',
                    style: TextStyle(
                        fontSize: 12, fontWeight: FontWeight.w700,
                        color: _statusColor(visit.status))),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  visit.store?.name ?? 'Store #${visit.storeId}',
                  style: tt.titleSmall?.copyWith(
                      color: AppColors.onSurface, fontWeight: FontWeight.w700),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: _statusBg(visit.status),
                  borderRadius: BorderRadius.circular(100),
                ),
                child: Text(visit.status.name.toUpperCase(),
                    style: TextStyle(
                        color: _statusColor(visit.status),
                        fontSize: 9, fontWeight: FontWeight.w700)),
              ),
            ],
          ),
          if (visit.durationMinutes != null)
            Padding(
              padding: const EdgeInsets.only(top: 4, left: 38),
              child: Text(
                'Duration: ${visit.durationMinutes} min',
                style: tt.bodySmall?.copyWith(color: AppColors.onSurfaceVariant),
              ),
            ),
          if (visit.skipReason != null)
            Padding(
              padding: const EdgeInsets.only(top: 4, left: 38),
              child: Text(
                'Reason: ${visit.skipReason}',
                style: tt.bodySmall?.copyWith(color: AppColors.error),
              ),
            ),
          // Action buttons
          if (visit.status == VisitStatus.pending ||
              visit.status == VisitStatus.checkedIn)
            Padding(
              padding: const EdgeInsets.only(top: 10, left: 38),
              child: Row(
                children: [
                  if (visit.status == VisitStatus.pending) ...[GestureDetector(
                    onTap: loading ? null : onCheckIn,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                      decoration: BoxDecoration(
                        color: AppColors.primary,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Text('Check In',
                          style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w700)),
                    ),
                  ),
                  const SizedBox(width: 8),
                  GestureDetector(
                    onTap: loading ? null : onSkip,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                      decoration: BoxDecoration(
                        color: AppColors.errorContainer,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Text('Skip',
                          style: TextStyle(color: AppColors.error, fontSize: 12, fontWeight: FontWeight.w700)),
                    ),
                  )],
                  if (visit.status == VisitStatus.checkedIn)
                    GestureDetector(
                      onTap: loading ? null : onCheckOut,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                        decoration: BoxDecoration(
                          color: AppColors.secondary,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Text('Check Out',
                            style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w700)),
                      ),
                    ),
                ],
              ),
            ),
        ],
      ),
    );
  }
}
