import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../../core/theme/app_theme.dart';
import '../../../../core/enums/route_status.dart';
import '../../../../core/enums/visit_status.dart';
import '../../../auth/presentation/providers/auth_provider.dart';
import '../../../gps_tracking/presentation/providers/gps_tracking_providers.dart';
import '../../../notifications/presentation/providers/notifications_providers.dart';
import '../../../routes/data/models/route_instance_model.dart';
import '../../../routes/presentation/providers/route_providers.dart';

// ─────────────────────────────────────────────────────────────
// Entry point
// ─────────────────────────────────────────────────────────────

// A simple notifier that lets child widgets switch tabs.
final _fieldForceTabProvider = StateProvider<int>((ref) => 0);

class FieldForceHomePage extends ConsumerWidget {
  const FieldForceHomePage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final selectedTab = ref.watch(_fieldForceTabProvider);
    final unreadAsync = ref.watch(unreadNotificationCountProvider);
    final unreadCount = unreadAsync.valueOrNull ?? 0;

    return Scaffold(
      body: IndexedStack(
        index: selectedTab,
        children: const [
          _TodayTab(),
          _NotificationsTab(),
          _ProfileTab(),
        ],
      ),
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          color: AppColors.surfaceContainerLowest,
          border: Border(top: BorderSide(color: AppColors.outlineVariant, width: 0.5)),
        ),
        child: NavigationBar(
          selectedIndex: selectedTab,
          backgroundColor: Colors.transparent,
          elevation: 0,
          onDestinationSelected: (i) =>
              ref.read(_fieldForceTabProvider.notifier).state = i,
          destinations: [
            const NavigationDestination(
              icon: Icon(Icons.today_outlined),
              selectedIcon: Icon(Icons.today_rounded),
              label: 'Today',
            ),
            NavigationDestination(
              icon: Badge(
                isLabelVisible: unreadCount > 0,
                label: Text('$unreadCount'),
                child: const Icon(Icons.notifications_outlined),
              ),
              selectedIcon: Badge(
                isLabelVisible: unreadCount > 0,
                label: Text('$unreadCount'),
                child: const Icon(Icons.notifications_rounded),
              ),
              label: 'Notifications',
            ),
            const NavigationDestination(
              icon: Icon(Icons.person_outline),
              selectedIcon: Icon(Icons.person_rounded),
              label: 'Profile',
            ),
          ],
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────
// Tab 0 — Today (Duty + Route)
// ─────────────────────────────────────────────────────────────

class _TodayTab extends ConsumerWidget {
  const _TodayTab();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authProvider).user;
    final trackingState = ref.watch(gpsTrackingControllerProvider);

    final unreadAsync = ref.watch(unreadNotificationCountProvider);
    final unreadCount = unreadAsync.valueOrNull ?? 0;
    final tt = Theme.of(context).textTheme;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0,
        scrolledUnderElevation: 0,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Good Morning',
              style: tt.bodySmall?.copyWith(color: AppColors.onSurfaceVariant),
            ),
            Text(
              user?.name.split(' ').first ?? 'there',
              style: tt.titleLarge?.copyWith(color: AppColors.onSurface),
            ),
          ],
        ),
        actions: [
          Stack(
            alignment: Alignment.center,
            children: [
              IconButton(
                icon: const Icon(Icons.notifications_outlined, size: 26),
                color: AppColors.onSurface,
                onPressed: () =>
                    ref.read(_fieldForceTabProvider.notifier).state = 1,
              ),
              if (unreadCount > 0)
                Positioned(
                  top: 10,
                  right: 10,
                  child: Container(
                    width: 8,
                    height: 8,
                    decoration: const BoxDecoration(
                      color: AppColors.error,
                      shape: BoxShape.circle,
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(width: 4),
        ],
      ),
      body: RefreshIndicator(
        color: AppColors.primary,
        onRefresh: () async {
          ref.invalidate(myRouteTodayProvider);
          ref.invalidate(activeDutyProvider);
          ref.invalidate(unreadNotificationCountProvider);
        },
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Duty card
            _DutyCard(trackingState: trackingState),
            const SizedBox(height: 16),
            // Today's route
            _TodayRouteSection(),
          ],
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────
// Duty Card
// ─────────────────────────────────────────────────────────────

class _DutyCard extends ConsumerWidget {
  const _DutyCard({required this.trackingState});

  final GpsTrackingState trackingState;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isActive = trackingState.isDutyActive;
    final tt = Theme.of(context).textTheme;

    return Container(
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLow,
        borderRadius: BorderRadius.circular(16),
      ),
      padding: const EdgeInsets.all(18),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 10,
                height: 10,
                decoration: BoxDecoration(
                  color: isActive ? AppColors.secondary : AppColors.outline,
                  shape: BoxShape.circle,
                ),
              ),
              const SizedBox(width: 8),
              Text(
                isActive ? 'On Duty' : 'Off Duty',
                style: tt.titleMedium?.copyWith(
                      fontWeight: FontWeight.w700,
                      color: isActive ? AppColors.secondary : AppColors.onSurfaceVariant,
                    ),
              ),
              const Spacer(),
              if (isActive && trackingState.lastPosition != null)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppColors.secondaryContainer,
                    borderRadius: BorderRadius.circular(100),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.gps_fixed, size: 12, color: AppColors.onSecondaryContainer),
                      const SizedBox(width: 4),
                      Text(
                        trackingState.lastPosition!.speed > 0
                            ? '${(trackingState.lastPosition!.speed * 3.6).toStringAsFixed(1)} km/h'
                            : 'GPS Active',
                        style: tt.labelSmall?.copyWith(
                          color: AppColors.onSecondaryContainer,
                          letterSpacing: 0,
                        ),
                      ),
                    ],
                  ),
                ),
            ],
          ),
          if (trackingState.error != null) ...[
              const SizedBox(height: 8),
              Text(
                trackingState.error!,
                style: const TextStyle(color: AppColors.error, fontSize: 12),
              ),
            ],
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: FilledButton.icon(
                onPressed: () async {
                  final controller =
                      ref.read(gpsTrackingControllerProvider.notifier);
                  if (isActive) {
                    final confirm = await showDialog<bool>(
                      context: context,
                      builder: (ctx) => AlertDialog(
                        title: const Text('End Duty?'),
                        content: const Text(
                          'GPS tracking will stop and your duty session will be recorded.',
                        ),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.pop(ctx, false),
                            child: const Text('Cancel'),
                          ),
                          FilledButton(
                            style: FilledButton.styleFrom(
                              backgroundColor: AppColors.error,
                            ),
                            onPressed: () => Navigator.pop(ctx, true),
                            child: const Text('End Duty'),
                          ),
                        ],
                      ),
                    );
                    if (confirm == true) await controller.endDuty();
                  } else {
                    await controller.startDuty();
                  }
                },
                icon: Icon(isActive ? Icons.stop_circle_outlined : Icons.play_circle_outline),
                label: Text(isActive ? 'End Duty' : 'Start Duty'),
                style: FilledButton.styleFrom(
                  backgroundColor: isActive ? AppColors.error : AppColors.secondary,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
            ),
          ],
        ),
      );
  }
}

// ─────────────────────────────────────────────────────────────
// Today's Route Section
// ─────────────────────────────────────────────────────────────

class _TodayRouteSection extends ConsumerWidget {
  const _TodayRouteSection();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final todayAsync = ref.watch(myRouteTodayProvider);

    return todayAsync.when(
      data: (instance) {
        if (instance == null) {
          return Container(
            padding: const EdgeInsets.all(32),
            decoration: BoxDecoration(
              color: AppColors.surfaceContainerLowest,
              borderRadius: BorderRadius.circular(16),
            ),
            child: Column(
              children: [
                Container(
                  width: 64,
                  height: 64,
                  decoration: BoxDecoration(
                    color: AppColors.surfaceContainerHigh,
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: const Icon(Icons.route_rounded, size: 32, color: AppColors.outline),
                ),
                const SizedBox(height: 12),
                Text(
                  'No route assigned for today',
                  style: Theme.of(context)
                      .textTheme
                      .bodyLarge
                      ?.copyWith(color: AppColors.onSurfaceVariant),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          );
        }
        return _RouteBody(instance: instance);
      },
      loading: () => const Center(
        child: Padding(
          padding: EdgeInsets.all(32),
          child: CircularProgressIndicator(),
        ),
      ),
      error: (e, _) => Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppColors.errorContainer,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Text('Error loading route: $e',
            style: const TextStyle(color: AppColors.error)),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────
// Route Body + Store Visit Cards
// ─────────────────────────────────────────────────────────────

class _RouteBody extends ConsumerStatefulWidget {
  const _RouteBody({required this.instance});

  final RouteInstance instance;

  @override
  ConsumerState<_RouteBody> createState() => _RouteBodyState();
}

class _RouteBodyState extends ConsumerState<_RouteBody> {
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
              labelText: 'Reason for skipping',
              border: OutlineInputBorder(),
            ),
            autofocus: true,
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

    if (reason == null || reason.trim().isEmpty) return;

    setState(() => _loading = true);
    try {
      final repo = ref.read(routeRepositoryProvider);
      await repo.skipVisit(visit.id, reason: reason.trim());
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

  void _navigateToStore(StoreVisit visit) {
    final lat = visit.store?.gpsLatitude;
    final lng = visit.store?.gpsLongitude;
    if (lat == null || lng == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Store has no GPS coordinates')),
      );
      return;
    }
    final uri = Uri.parse(
        'https://www.google.com/maps/dir/?api=1&destination=$lat,$lng&travelmode=driving');
    launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  @override
  Widget build(BuildContext context) {
    final instance = widget.instance;
    final theme = Theme.of(context);
    final notStarted = instance.status == RouteStatus.published;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Route header card
        Container(
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            color: AppColors.surfaceContainerLowest,
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(
                      instance.routePlanName ?? 'Route #${instance.routePlanId}',
                      style: Theme.of(context).textTheme.titleMedium
                          ?.copyWith(fontWeight: FontWeight.w700, color: AppColors.onSurface),
                    ),
                  ),
                  _StatusBadge(status: instance.status),
                ],
              ),
              const SizedBox(height: 14),
              ClipRRect(
                borderRadius: BorderRadius.circular(100),
                child: LinearProgressIndicator(
                  value: instance.completionPercentage / 100,
                  backgroundColor: AppColors.surfaceContainerHigh,
                  valueColor: const AlwaysStoppedAnimation<Color>(AppColors.primary),
                  minHeight: 7,
                ),
              ),
              const SizedBox(height: 6),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    '${instance.completedVisits} of ${instance.totalVisits} visits',
                    style: Theme.of(context).textTheme.bodySmall
                        ?.copyWith(color: AppColors.onSurfaceVariant),
                  ),
                  Text(
                    '${instance.completionPercentage.toStringAsFixed(0)}%',
                    style: Theme.of(context).textTheme.labelSmall?.copyWith(
                        color: AppColors.primary, fontWeight: FontWeight.w700, letterSpacing: 0),
                  ),
                ],
              ),
              if (notStarted) ...[
                const SizedBox(height: 14),
                SizedBox(
                  width: double.infinity,
                  child: FilledButton.icon(
                    onPressed: _loading ? null : _startRoute,
                    icon: _loading
                        ? const SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(
                                strokeWidth: 2, color: Colors.white),
                          )
                        : const Icon(Icons.play_arrow_rounded),
                    label: const Text('Start Route'),
                  ),
                ),
              ],
            ],
          ),
        ),

        const SizedBox(height: 8),

        // Store visits
        if (instance.visits.isNotEmpty) ...[
          const SizedBox(height: 16),
          Padding(
            padding: const EdgeInsets.only(bottom: 10),
            child: Text(
              'Store Visits',
              style: Theme.of(context).textTheme.titleMedium
                  ?.copyWith(fontWeight: FontWeight.w700, color: AppColors.onSurface),
            ),
          ),
          ...instance.visits.map(
            (visit) => _StoreVisitCard(
              visit: visit,
              index: visit.visitOrder,
              onCheckIn: _loading ? null : () => _checkIn(visit),
              onCheckOut: _loading ? null : () => _checkOut(visit),
              onSkip: _loading ? null : () => _skipVisit(visit),
              onNavigate: () => _navigateToStore(visit),
              onOpenStore: () => context.push('/stores/${visit.storeId}'),
            ),
          ),
        ],
      ],
    );
  }
}

// ─────────────────────────────────────────────────────────────
// Store Visit Card
// ─────────────────────────────────────────────────────────────

class _StoreVisitCard extends StatelessWidget {
  const _StoreVisitCard({
    required this.visit,
    required this.index,
    required this.onNavigate,
    required this.onOpenStore,
    this.onCheckIn,
    this.onCheckOut,
    this.onSkip,
  });

  final StoreVisit visit;
  final int index;
  final VoidCallback? onCheckIn;
  final VoidCallback? onCheckOut;
  final VoidCallback? onSkip;
  final VoidCallback onNavigate;
  final VoidCallback onOpenStore;

  @override
  Widget build(BuildContext context) {
    final store = visit.store;
    final isCompleted = visit.status == VisitStatus.completed;
    final isCheckedIn = visit.status == VisitStatus.checkedIn;
    final isSkipped = visit.status == VisitStatus.skipped;
    final isPending = visit.status == VisitStatus.pending;
    final tt = Theme.of(context).textTheme;

    final borderColor = isCompleted
        ? AppColors.secondary
        : isCheckedIn
            ? AppColors.primaryContainer
            : AppColors.outlineVariant;

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest,
        borderRadius: BorderRadius.circular(14),
        border: Border(left: BorderSide(color: borderColor, width: 4)),
      ),
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header row
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Visit number badge
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  color: isCompleted
                      ? AppColors.secondary
                      : isSkipped
                          ? AppColors.outline
                          : AppColors.primary,
                  shape: BoxShape.circle,
                ),
                alignment: Alignment.center,
                child: isCompleted
                    ? const Icon(Icons.check_rounded, color: Colors.white, size: 18)
                    : isSkipped
                        ? const Icon(Icons.skip_next_rounded, color: Colors.white, size: 18)
                        : Text(
                            '$index',
                            style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.w700,
                                fontSize: 14),
                          ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    GestureDetector(
                      onTap: onOpenStore,
                      child: Text(
                        store?.name ?? 'Store #${visit.storeId}',
                        style: tt.titleSmall?.copyWith(
                          fontWeight: FontWeight.w700,
                          color: AppColors.primary,
                          decoration: TextDecoration.underline,
                          decorationColor: AppColors.primary,
                        ),
                      ),
                    ),
                    if (store?.address != null)
                      Text(
                        store!.address!,
                        style: tt.bodySmall?.copyWith(color: AppColors.onSurfaceVariant),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    if (store?.areaName != null)
                      Text(
                        store!.areaName!,
                        style: tt.bodySmall?.copyWith(color: AppColors.outline),
                      ),
                  ],
                ),
              ),
              _VisitStatusChip(status: visit.status),
            ],
          ),

            // Check-in/out times
            if (visit.checkedInAt != null || visit.checkedOutAt != null) ...[
              const SizedBox(height: 8),
              const Divider(height: 1),
              const SizedBox(height: 6),
              if (visit.checkedInAt != null)
                _TimeRow(
                  icon: Icons.login,
                  label: 'Checked In',
                  time: visit.checkedInAt!,
                  color: AppColors.primaryContainer,
                ),
              if (visit.checkedOutAt != null)
                _TimeRow(
                  icon: Icons.logout,
                  label: 'Checked Out',
                  time: visit.checkedOutAt!,
                  color: AppColors.secondary,
                ),
              if (visit.durationMinutes != null)
                _TimeRow(
                  icon: Icons.timer_outlined,
                  label: 'Duration',
                  time: '${visit.durationMinutes} min',
                  color: AppColors.tertiary,
                ),
            ],

            if (isSkipped && visit.skipReason != null) ...[
              const SizedBox(height: 6),
              Text(
                'Reason: ${visit.skipReason}',
                style: const TextStyle(
                    fontSize: 12, color: AppColors.onSurfaceVariant, fontStyle: FontStyle.italic),
              ),
            ],

            // Action buttons
            if (!isCompleted && !isSkipped) ...[
              const SizedBox(height: 10),
              Wrap(
                spacing: 8,
                runSpacing: 6,
                children: [
                  // Navigate to store (always available)
                  if (store?.gpsLatitude != null)
                    _ActionButton(
                      icon: Icons.navigation_outlined,
                      label: 'Navigate',
                      color: AppColors.primary,
                      onPressed: onNavigate,
                    ),
                  // Check-in (only when pending, route in progress)
                  if (isPending)
                    _ActionButton(
                      icon: Icons.login,
                      label: 'Check In',
                      color: AppColors.primaryContainer,
                      onPressed: onCheckIn,
                    ),
                  // Check-out (only when checked-in)
                  if (isCheckedIn) ...[
                    _ActionButton(
                      icon: Icons.logout,
                      label: 'Check Out',
                      color: AppColors.secondary,
                      onPressed: onCheckOut,
                    ),
                    _ActionButton(
                      icon: Icons.point_of_sale,
                      label: 'Create Order',
                      color: AppColors.tertiary,
                      onPressed: () => context.push('/sales/create'),
                    ),
                  ],
                  // Skip (only when pending)
                  if (isPending)
                    _ActionButton(
                      icon: Icons.skip_next,
                      label: 'Skip',
                      color: AppColors.outline,
                      onPressed: onSkip,
                    ),
                ],
              ),
            ],
          ],
        ),
      );
  }
}

// ─────────────────────────────────────────────────────────────
// Tab 1 — Notifications
// ─────────────────────────────────────────────────────────────

class _NotificationsTab extends ConsumerWidget {
  const _NotificationsTab();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final notificationsAsync = ref.watch(myNotificationsProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
        actions: [
          TextButton(
            onPressed: () async {
              final repo = ref.read(notificationsRepositoryProvider);
              await repo.markAllRead();
              ref.invalidate(myNotificationsProvider);
              ref.invalidate(unreadNotificationCountProvider);
            },
            child: const Text('Mark All Read'),
          ),
        ],
      ),
      backgroundColor: AppColors.background,
      body: notificationsAsync.when(
        data: (notifications) => notifications.isEmpty
            ? Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      width: 64,
                      height: 64,
                      decoration: BoxDecoration(
                        color: AppColors.surfaceContainerHigh,
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: const Icon(Icons.notifications_none_rounded,
                          size: 32, color: AppColors.outline),
                    ),
                    const SizedBox(height: 12),
                    Text('No notifications',
                        style: Theme.of(context)
                            .textTheme
                            .bodyLarge
                            ?.copyWith(color: AppColors.onSurfaceVariant)),
                  ],
                ),
              )
            : RefreshIndicator(
                color: AppColors.primary,
                onRefresh: () async {
                  ref.invalidate(myNotificationsProvider);
                  ref.invalidate(unreadNotificationCountProvider);
                },
                child: ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: notifications.length,
                  itemBuilder: (context, i) {
                    final n = notifications[i];
                    return Container(
                      margin: const EdgeInsets.only(bottom: 8),
                      decoration: BoxDecoration(
                        color: n.isRead
                            ? AppColors.surfaceContainerLowest
                            : AppColors.surfaceContainerLow,
                        borderRadius: BorderRadius.circular(12),
                        border: n.isRead
                            ? null
                            : const Border(
                                left: BorderSide(
                                    color: AppColors.primaryContainer,
                                    width: 3)),
                      ),
                      child: ListTile(
                        contentPadding: const EdgeInsets.symmetric(
                            horizontal: 14, vertical: 4),
                        leading: Container(
                          width: 36,
                          height: 36,
                          decoration: BoxDecoration(
                            color: n.isRead
                                ? AppColors.surfaceContainerHigh
                                : AppColors.primaryContainer.withOpacity(0.2),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Icon(n.type.icon, size: 18,
                              color: n.isRead ? AppColors.outline : AppColors.primaryContainer),
                        ),
                        title: Text(n.title,
                            style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                fontWeight: n.isRead ? FontWeight.w500 : FontWeight.w700,
                                color: AppColors.onSurface)),
                        subtitle: n.body != null
                            ? Text(n.body!,
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                                style: Theme.of(context).textTheme.bodySmall
                                    ?.copyWith(color: AppColors.onSurfaceVariant))
                            : null,
                        trailing: !n.isRead
                            ? Container(
                                width: 8,
                                height: 8,
                                decoration: const BoxDecoration(
                                  color: AppColors.primary,
                                  shape: BoxShape.circle,
                                ),
                              )
                            : null,
                        onTap: () async {
                          if (!n.isRead) {
                            final repo = ref.read(notificationsRepositoryProvider);
                            await repo.markAllRead();
                            ref.invalidate(myNotificationsProvider);
                            ref.invalidate(unreadNotificationCountProvider);
                          }
                        },
                      ),
                    );
                  },
                ),
              ),
        loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────
// Tab 2 — Profile
// ─────────────────────────────────────────────────────────────

class _ProfileTab extends ConsumerWidget {
  const _ProfileTab();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authProvider).user;
    final trackingState = ref.watch(gpsTrackingControllerProvider);
    final tt = Theme.of(context).textTheme;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0,
        title: Text('Profile', style: tt.titleLarge?.copyWith(color: AppColors.onSurface)),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Avatar + name card
          Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: AppColors.surfaceContainerLowest,
              borderRadius: BorderRadius.circular(16),
            ),
            child: Column(
              children: [
                Container(
                  width: 72,
                  height: 72,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                        colors: [AppColors.primary, AppColors.primaryContainer]),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  alignment: Alignment.center,
                  child: Text(
                    _initials(user?.name ?? ''),
                    style: const TextStyle(
                        color: Colors.white, fontSize: 28, fontWeight: FontWeight.w800, fontFamily: 'Manrope'),
                  ),
                ),
                const SizedBox(height: 14),
                Text(
                  user?.name ?? '-',
                  style: tt.headlineSmall?.copyWith(color: AppColors.onSurface),
                ),
                const SizedBox(height: 4),
                Text(
                  user?.email ?? '',
                  style: tt.bodyMedium?.copyWith(color: AppColors.onSurfaceVariant),
                ),
                if (user?.role != null) ...[
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                    decoration: BoxDecoration(
                      color: AppColors.secondaryContainer,
                      borderRadius: BorderRadius.circular(100),
                    ),
                    child: Text(
                      _roleLabel(user!.role!),
                      style: tt.labelMedium?.copyWith(color: AppColors.onSecondaryContainer),
                    ),
                  ),
                ],
              ],
            ),
          ),

          const SizedBox(height: 12),

          // GPS tracking status
          Container(
            decoration: BoxDecoration(
              color: AppColors.surfaceContainerLowest,
              borderRadius: BorderRadius.circular(12),
            ),
            child: ListTile(
              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              leading: Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  color: trackingState.isDutyActive
                      ? AppColors.secondaryContainer
                      : AppColors.surfaceContainerHigh,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(
                  trackingState.isDutyActive ? Icons.gps_fixed : Icons.gps_off,
                  color: trackingState.isDutyActive
                      ? AppColors.secondary
                      : AppColors.outline,
                  size: 18,
                ),
              ),
              title: Text(
                trackingState.isDutyActive ? 'On Duty' : 'Off Duty',
                style: tt.titleSmall?.copyWith(color: AppColors.onSurface),
              ),
              subtitle: Text(
                trackingState.isDutyActive
                    ? 'GPS tracking is active'
                    : 'Start duty on the Today tab',
                style: tt.bodySmall?.copyWith(color: AppColors.onSurfaceVariant),
              ),
            ),
          ),

          const SizedBox(height: 4),

          // Settings
          Container(
            decoration: BoxDecoration(
              color: AppColors.surfaceContainerLowest,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Column(
              children: [
                _ProfileTile(icon: Icons.language_rounded, label: 'Language', onTap: () => context.push('/language')),
                Divider(color: AppColors.outlineVariant.withOpacity(0.5), height: 1, indent: 54),
                _ProfileTile(icon: Icons.sync_rounded, label: 'Sync Status', onTap: () => context.push('/sync')),
                Divider(color: AppColors.outlineVariant.withOpacity(0.5), height: 1, indent: 54),
                _ProfileTile(icon: Icons.checklist_rounded, label: 'My Assigned Tasks', onTap: () => context.push('/assigned-tasks')),
                Divider(color: AppColors.outlineVariant.withOpacity(0.5), height: 1, indent: 54),
                _ProfileTile(icon: Icons.receipt_long_rounded, label: 'My Orders', onTap: () => context.push('/my-orders')),
              ],
            ),
          ),

          const SizedBox(height: 24),

          // Logout
          GestureDetector(
            onTap: () async {
              final confirm = await showDialog<bool>(
                context: context,
                builder: (ctx) => AlertDialog(
                  title: const Text('Logout'),
                  content: const Text('Are you sure you want to logout?'),
                  actions: [
                    TextButton(
                      onPressed: () => Navigator.pop(ctx, false),
                      child: const Text('Cancel'),
                    ),
                    FilledButton(
                      style: FilledButton.styleFrom(backgroundColor: AppColors.error),
                      onPressed: () => Navigator.pop(ctx, true),
                      child: const Text('Logout'),
                    ),
                  ],
                ),
              );
              if (confirm == true) {
                await ref.read(authProvider.notifier).logout();
                if (context.mounted) context.go('/login');
              }
            },
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 16),
              decoration: BoxDecoration(
                color: AppColors.errorContainer,
                borderRadius: BorderRadius.circular(12),
              ),
              alignment: Alignment.center,
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.logout_rounded, color: AppColors.onErrorContainer, size: 18),
                  const SizedBox(width: 8),
                  Text('Logout',
                      style: tt.labelLarge?.copyWith(color: AppColors.onErrorContainer)),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  String _initials(String name) {
    final parts = name.trim().split(' ');
    if (parts.isEmpty) return '?';
    if (parts.length == 1) return parts[0][0].toUpperCase();
    return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
  }

  String _roleLabel(String role) {
    return switch (role) {
      'field_force' => 'Field Force',
      'sales_representative' => 'Sales Representative',
      'promoter' => 'Promoter',
      'merchandiser' => 'Merchandiser',
      'team_leader' => 'Team Leader',
      'administrator' => 'Administrator',
      'super_admin' => 'Super Admin',
      _ => role,
    };
  }
}

// ─────────────────────────────────────────────────────────────
// Small widgets
// ─────────────────────────────────────────────────────────────

class _StatusBadge extends StatelessWidget {
  const _StatusBadge({required this.status});

  final RouteStatus status;

  @override
  Widget build(BuildContext context) {
    final (color, bg) = switch (status) {
      RouteStatus.inProgress => (AppColors.primaryContainer, AppColors.primaryContainer.withOpacity(0.15)),
      RouteStatus.completed => (AppColors.secondary, AppColors.secondaryContainer),
      RouteStatus.published => (AppColors.tertiary, AppColors.tertiaryContainer.withOpacity(0.25)),
      RouteStatus.cancelled => (AppColors.error, AppColors.errorContainer),
      _ => (AppColors.onSurfaceVariant, AppColors.surfaceContainerHigh),
    };
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(100),
      ),
      child: Text(
        status.label,
        style: TextStyle(
            color: color, fontSize: 11, fontWeight: FontWeight.w700),
      ),
    );
  }
}

class _ProfileTile extends StatelessWidget {
  const _ProfileTile({required this.icon, required this.label, required this.onTap});
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 2),
      leading: Container(
        width: 36,
        height: 36,
        decoration: BoxDecoration(
          color: AppColors.surfaceContainerLow,
          borderRadius: BorderRadius.circular(10),
        ),
        child: Icon(icon, color: AppColors.onSurfaceVariant, size: 18),
      ),
      title: Text(label,
          style: Theme.of(context)
              .textTheme
              .bodyMedium
              ?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w500)),
      trailing: const Icon(Icons.chevron_right_rounded,
          color: AppColors.outline, size: 18),
      onTap: onTap,
    );
  }
}

class _VisitStatusChip extends StatelessWidget {
  const _VisitStatusChip({required this.status});

  final VisitStatus status;

  @override
  Widget build(BuildContext context) {
    final (color, bg, icon) = switch (status) {
      VisitStatus.pending => (AppColors.onSurfaceVariant, AppColors.surfaceContainerHigh, Icons.circle_outlined),
      VisitStatus.checkedIn => (AppColors.primaryContainer, AppColors.primaryContainer.withOpacity(0.15), Icons.login_rounded),
      VisitStatus.completed => (AppColors.secondary, AppColors.secondaryContainer, Icons.check_circle_rounded),
      VisitStatus.skipped => (AppColors.error, AppColors.errorContainer, Icons.skip_next_rounded),
    };
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(100)),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 11, color: color),
          const SizedBox(width: 3),
          Text(status.label,
              style: TextStyle(
                  color: color, fontSize: 10, fontWeight: FontWeight.w700)),
        ],
      ),
    );
  }
}

class _ActionButton extends StatelessWidget {
  const _ActionButton({
    required this.icon,
    required this.label,
    required this.color,
    required this.onPressed,
  });

  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback? onPressed;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onPressed,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
        decoration: BoxDecoration(
          color: color.withOpacity(0.1),
          borderRadius: BorderRadius.circular(100),
          border: Border.all(color: color.withOpacity(0.3)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 14, color: color),
            const SizedBox(width: 5),
            Text(label,
                style: TextStyle(
                    color: color, fontSize: 12, fontWeight: FontWeight.w600, fontFamily: 'Inter')),
          ],
        ),
      ),
    );
  }
}

class _TimeRow extends StatelessWidget {
  const _TimeRow({
    required this.icon,
    required this.label,
    required this.time,
    required this.color,
  });

  final IconData icon;
  final String label;
  final String time;
  final Color color;

  @override
  Widget build(BuildContext context) {
    // Format ISO time to readable
    String displayTime = time;
    try {
      final dt = DateTime.parse(time);
      displayTime =
          '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
    } catch (_) {}

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(
        children: [
          Icon(icon, size: 14, color: color),
          const SizedBox(width: 6),
          Text('$label: ',
              style: const TextStyle(fontSize: 12, color: AppColors.onSurfaceVariant)),
          Text(displayTime,
              style: TextStyle(
                  fontSize: 12, color: color, fontWeight: FontWeight.w500)),
        ],
      ),
    );
  }
}
