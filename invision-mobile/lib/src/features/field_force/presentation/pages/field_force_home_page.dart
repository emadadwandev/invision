import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

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
      bottomNavigationBar: NavigationBar(
        selectedIndex: selectedTab,
        onDestinationSelected: (i) =>
            ref.read(_fieldForceTabProvider.notifier).state = i,
        destinations: [
          const NavigationDestination(
            icon: Icon(Icons.today_outlined),
            selectedIcon: Icon(Icons.today),
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
              child: const Icon(Icons.notifications),
            ),
            label: 'Notifications',
          ),
          const NavigationDestination(
            icon: Icon(Icons.person_outline),
            selectedIcon: Icon(Icons.person),
            label: 'Profile',
          ),
        ],
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

    return Scaffold(
      appBar: AppBar(
        title: Text('Hi, ${user?.name.split(' ').first ?? 'there'} 👋'),
        centerTitle: false,
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications_outlined),
            onPressed: () =>
                ref.read(_fieldForceTabProvider.notifier).state = 1,
          ),
        ],
      ),
      body: RefreshIndicator(
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
    final color = isActive ? Colors.green : Colors.grey;

    return Card(
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 10,
                  height: 10,
                  decoration: BoxDecoration(
                    color: color,
                    shape: BoxShape.circle,
                  ),
                ),
                const SizedBox(width: 8),
                Text(
                  isActive ? 'On Duty' : 'Off Duty',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: color,
                      ),
                ),
                const Spacer(),
                if (isActive && trackingState.lastPosition != null)
                  Chip(
                    avatar: const Icon(Icons.gps_fixed, size: 16),
                    label: Text(
                      trackingState.lastPosition!.speed > 0
                          ? '${(trackingState.lastPosition!.speed * 3.6).toStringAsFixed(1)} km/h'
                          : 'GPS Active',
                      style: const TextStyle(fontSize: 11),
                    ),
                    backgroundColor: Colors.green.shade50,
                    padding: EdgeInsets.zero,
                  ),
              ],
            ),
            if (trackingState.error != null) ...[
              const SizedBox(height: 8),
              Text(
                trackingState.error!,
                style: const TextStyle(color: Colors.red, fontSize: 12),
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
                              backgroundColor: Colors.red,
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
                  backgroundColor: isActive ? Colors.red : Colors.green,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                ),
              ),
            ),
          ],
        ),
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
          return Card(
            child: Padding(
              padding: const EdgeInsets.all(32),
              child: Column(
                children: [
                  Icon(Icons.route, size: 56, color: Colors.grey.shade400),
                  const SizedBox(height: 12),
                  Text(
                    'No route assigned for today',
                    style: Theme.of(context)
                        .textTheme
                        .bodyLarge
                        ?.copyWith(color: Colors.grey),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
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
      error: (e, _) => Card(
        color: Colors.red.shade50,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Text('Error loading route: $e',
              style: const TextStyle(color: Colors.red)),
        ),
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
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        instance.routePlanName ?? 'Route #${instance.routePlanId}',
                        style: theme.textTheme.titleMedium
                            ?.copyWith(fontWeight: FontWeight.bold),
                      ),
                    ),
                    _StatusBadge(status: instance.status),
                  ],
                ),
                const SizedBox(height: 12),
                // Progress bar
                ClipRRect(
                  borderRadius: BorderRadius.circular(4),
                  child: LinearProgressIndicator(
                    value: instance.completionPercentage / 100,
                    backgroundColor: Colors.grey.shade200,
                    minHeight: 8,
                  ),
                ),
                const SizedBox(height: 6),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      '${instance.completedVisits} of ${instance.totalVisits} visits',
                      style: theme.textTheme.bodySmall,
                    ),
                    Text(
                      '${instance.completionPercentage.toStringAsFixed(0)}%',
                      style: theme.textTheme.bodySmall
                          ?.copyWith(fontWeight: FontWeight.bold),
                    ),
                  ],
                ),
                if (notStarted) ...[
                  const SizedBox(height: 12),
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
                          : const Icon(Icons.start),
                      label: const Text('Start Route'),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ),

        const SizedBox(height: 8),

        // Store visits
        if (instance.visits.isNotEmpty) ...[
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 8),
            child: Text(
              'Store Visits',
              style: theme.textTheme.titleSmall
                  ?.copyWith(fontWeight: FontWeight.bold, color: Colors.grey[700]),
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
    final theme = Theme.of(context);
    final store = visit.store;
    final isCompleted = visit.status == VisitStatus.completed;
    final isCheckedIn = visit.status == VisitStatus.checkedIn;
    final isSkipped = visit.status == VisitStatus.skipped;
    final isPending = visit.status == VisitStatus.pending;

    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      color: isCompleted
          ? Colors.green.shade50
          : isSkipped
              ? Colors.grey.shade100
              : null,
      child: Padding(
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
                        ? Colors.green
                        : isSkipped
                            ? Colors.grey
                            : theme.colorScheme.primary,
                    shape: BoxShape.circle,
                  ),
                  alignment: Alignment.center,
                  child: isCompleted
                      ? const Icon(Icons.check, color: Colors.white, size: 18)
                      : isSkipped
                          ? const Icon(Icons.skip_next,
                              color: Colors.white, size: 18)
                          : Text(
                              '$index',
                              style: const TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.bold,
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
                          style: theme.textTheme.titleSmall?.copyWith(
                            fontWeight: FontWeight.bold,
                            decoration: TextDecoration.underline,
                          ),
                        ),
                      ),
                      if (store?.address != null)
                        Text(
                          store!.address!,
                          style: theme.textTheme.bodySmall
                              ?.copyWith(color: Colors.grey[600]),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      if (store?.areaName != null)
                        Text(
                          store!.areaName!,
                          style: theme.textTheme.bodySmall
                              ?.copyWith(color: Colors.grey[500]),
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
                  color: Colors.blue,
                ),
              if (visit.checkedOutAt != null)
                _TimeRow(
                  icon: Icons.logout,
                  label: 'Checked Out',
                  time: visit.checkedOutAt!,
                  color: Colors.green,
                ),
              if (visit.durationMinutes != null)
                _TimeRow(
                  icon: Icons.timer_outlined,
                  label: 'Duration',
                  time: '${visit.durationMinutes} min',
                  color: Colors.orange,
                ),
            ],

            if (isSkipped && visit.skipReason != null) ...[
              const SizedBox(height: 6),
              Text(
                'Reason: ${visit.skipReason}',
                style: TextStyle(
                    fontSize: 12, color: Colors.grey[600], fontStyle: FontStyle.italic),
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
                      color: Colors.indigo,
                      onPressed: onNavigate,
                    ),
                  // Check-in (only when pending, route in progress)
                  if (isPending)
                    _ActionButton(
                      icon: Icons.login,
                      label: 'Check In',
                      color: Colors.blue,
                      onPressed: onCheckIn,
                    ),
                  // Check-out (only when checked-in)
                  if (isCheckedIn) ...[
                    _ActionButton(
                      icon: Icons.logout,
                      label: 'Check Out',
                      color: Colors.green,
                      onPressed: onCheckOut,
                    ),
                    _ActionButton(
                      icon: Icons.point_of_sale,
                      label: 'Create Order',
                      color: Colors.orange,
                      onPressed: () => context.push('/sales/create'),
                    ),
                  ],
                  // Skip (only when pending)
                  if (isPending)
                    _ActionButton(
                      icon: Icons.skip_next,
                      label: 'Skip',
                      color: Colors.grey,
                      onPressed: onSkip,
                    ),
                ],
              ),
            ],
          ],
        ),
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
      body: notificationsAsync.when(
        data: (notifications) => notifications.isEmpty
            ? const Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.notifications_none, size: 56, color: Colors.grey),
                    SizedBox(height: 12),
                    Text('No notifications'),
                  ],
                ),
              )
            : RefreshIndicator(
                onRefresh: () async {
                  ref.invalidate(myNotificationsProvider);
                  ref.invalidate(unreadNotificationCountProvider);
                },
                child: ListView.builder(
                  padding: const EdgeInsets.all(12),
                  itemCount: notifications.length,
                  itemBuilder: (context, i) {
                    final n = notifications[i];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 8),
                      color: n.isRead
                          ? null
                          : Theme.of(context)
                              .colorScheme
                              .primaryContainer
                              .withAlpha(40),
                      child: ListTile(
                        leading: Icon(
                          n.type.icon,
                          color: n.isRead ? Colors.grey : n.type.color,
                        ),
                        title: Text(n.title,
                            style: TextStyle(
                                fontWeight: n.isRead
                                    ? FontWeight.normal
                                    : FontWeight.bold)),
                        subtitle: n.body != null
                            ? Text(n.body!, maxLines: 2, overflow: TextOverflow.ellipsis)
                            : null,
                        trailing: !n.isRead
                            ? Container(
                                width: 8,
                                height: 8,
                                decoration: BoxDecoration(
                                  color: Theme.of(context).colorScheme.primary,
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
        loading: () => const Center(child: CircularProgressIndicator()),
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

    return Scaffold(
      appBar: AppBar(title: const Text('Profile')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Avatar + name card
          Card(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                children: [
                  CircleAvatar(
                    radius: 36,
                    backgroundColor: Theme.of(context).colorScheme.primary,
                    child: Text(
                      _initials(user?.name ?? ''),
                      style: const TextStyle(
                          color: Colors.white,
                          fontSize: 24,
                          fontWeight: FontWeight.bold),
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    user?.name ?? '-',
                    style: Theme.of(context)
                        .textTheme
                        .titleLarge
                        ?.copyWith(fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    user?.email ?? '',
                    style: Theme.of(context)
                        .textTheme
                        .bodyMedium
                        ?.copyWith(color: Colors.grey[600]),
                  ),
                  if (user?.role != null) ...[
                    const SizedBox(height: 8),
                    Chip(
                      label: Text(_roleLabel(user!.role!)),
                      backgroundColor:
                          Theme.of(context).colorScheme.secondaryContainer,
                    ),
                  ],
                ],
              ),
            ),
          ),

          const SizedBox(height: 12),

          // GPS tracking status
          Card(
            child: ListTile(
              leading: Icon(
                trackingState.isDutyActive ? Icons.gps_fixed : Icons.gps_off,
                color: trackingState.isDutyActive ? Colors.green : Colors.grey,
              ),
              title: Text(
                  trackingState.isDutyActive ? 'On Duty' : 'Off Duty'),
              subtitle: Text(trackingState.isDutyActive
                  ? 'GPS tracking is active'
                  : 'Start duty on the Today tab'),
            ),
          ),

          const SizedBox(height: 4),

          // Settings
          Card(
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.language),
                  title: const Text('Language'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => context.push('/language'),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.sync),
                  title: const Text('Sync Status'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => context.push('/sync'),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.checklist),
                  title: const Text('My Assigned Tasks'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => context.push('/assigned-tasks'),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.receipt_long),
                  title: const Text('My Orders'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => context.push('/my-orders'),
                ),
              ],
            ),
          ),

          const SizedBox(height: 16),

          // Logout
          OutlinedButton.icon(
            onPressed: () async {
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
                      style: FilledButton.styleFrom(
                          backgroundColor: Colors.red),
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
            icon: const Icon(Icons.logout, color: Colors.red),
            label: const Text('Logout', style: TextStyle(color: Colors.red)),
            style: OutlinedButton.styleFrom(
              side: const BorderSide(color: Colors.red),
              padding: const EdgeInsets.symmetric(vertical: 14),
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
      RouteStatus.inProgress => (Colors.blue.shade800, Colors.blue.shade100),
      RouteStatus.completed => (Colors.green.shade800, Colors.green.shade100),
      RouteStatus.published => (Colors.orange.shade800, Colors.orange.shade100),
      RouteStatus.cancelled => (Colors.red.shade800, Colors.red.shade100),
      _ => (Colors.grey.shade800, Colors.grey.shade200),
    };
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        status.label,
        style: TextStyle(
            color: color, fontSize: 11, fontWeight: FontWeight.w600),
      ),
    );
  }
}

class _VisitStatusChip extends StatelessWidget {
  const _VisitStatusChip({required this.status});

  final VisitStatus status;

  @override
  Widget build(BuildContext context) {
    final (color, bg, icon) = switch (status) {
      VisitStatus.pending => (Colors.grey.shade700, Colors.grey.shade200, Icons.circle_outlined),
      VisitStatus.checkedIn => (Colors.blue.shade800, Colors.blue.shade100, Icons.login),
      VisitStatus.completed => (Colors.green.shade800, Colors.green.shade100, Icons.check_circle),
      VisitStatus.skipped => (Colors.red.shade800, Colors.red.shade100, Icons.skip_next),
    };
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
          color: bg, borderRadius: BorderRadius.circular(10)),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 11, color: color),
          const SizedBox(width: 3),
          Text(status.label,
              style: TextStyle(
                  color: color,
                  fontSize: 10,
                  fontWeight: FontWeight.w600)),
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
    return OutlinedButton.icon(
      onPressed: onPressed,
      icon: Icon(icon, size: 16, color: color),
      label: Text(label, style: TextStyle(color: color, fontSize: 12)),
      style: OutlinedButton.styleFrom(
        side: BorderSide(color: color.withAlpha(120)),
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        minimumSize: Size.zero,
        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
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
              style: TextStyle(fontSize: 12, color: Colors.grey[600])),
          Text(displayTime,
              style: TextStyle(
                  fontSize: 12, color: color, fontWeight: FontWeight.w500)),
        ],
      ),
    );
  }
}
