import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../../core/theme/app_theme.dart';
import '../../../../core/enums/route_status.dart';
import '../../../../core/enums/visit_status.dart';
import '../../../auth/presentation/providers/auth_provider.dart';
import '../../../gps_tracking/presentation/providers/gps_tracking_providers.dart';
import '../../../notifications/presentation/providers/notifications_providers.dart';
import '../../../offline/presentation/providers/offline_providers.dart';
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
        children: const [_TodayTab(), _NotificationsTab(), _ProfileTab()],
      ),
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          color: AppColors.surfaceContainerLowest,
          border: Border(
            top: BorderSide(color: AppColors.outlineVariant, width: 0.5),
          ),
        ),
        child: NavigationBar(
          selectedIndex: selectedTab,
          backgroundColor: Colors.transparent,
          elevation: 0,
          onDestinationSelected: (i) =>
              ref.read(_fieldForceTabProvider.notifier).state = i,
          destinations: [
            const NavigationDestination(
              icon: Icon(Icons.home_outlined),
              selectedIcon: Icon(Icons.home_rounded),
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
              label: 'Alerts',
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

    return Scaffold(
      backgroundColor: AppColors.background,
      body: RefreshIndicator(
        color: AppColors.primary,
        onRefresh: () async {
          ref.invalidate(myRouteTodayProvider);
          ref.invalidate(activeDutyProvider);
          ref.invalidate(unreadNotificationCountProvider);
        },
        child: SafeArea(
          top: false,
          bottom: false,
          child: ListView(
            padding: EdgeInsets.zero,
            children: [
              _TodayHeader(
                userName: user?.name ?? 'there',
                trackingState: trackingState,
              ),
              const Padding(
                padding: EdgeInsets.fromLTRB(16, 20, 16, 28),
                child: _TodayRouteSection(),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _TodayHeader extends ConsumerWidget {
  const _TodayHeader({required this.userName, required this.trackingState});

  final String userName;
  final GpsTrackingState trackingState;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isActive = trackingState.isDutyActive;
    final unreadCount =
        ref.watch(unreadNotificationCountProvider).valueOrNull ?? 0;
    final sync = ref.watch(syncStatusProvider);
    final firstName = userName.trim().split(' ').first;
    final hour = TimeOfDay.now().hour;
    final greeting = hour < 12
        ? 'Good morning'
        : hour < 17
        ? 'Good afternoon'
        : 'Good evening';

    String syncLabel;
    IconData syncIcon;
    if (sync.isLoading) {
      syncLabel = 'Checking sync';
      syncIcon = Icons.sync_rounded;
    } else if (sync.valueOrNull?.isOnline == false) {
      final pending = sync.valueOrNull?.pendingActions ?? 0;
      syncLabel = pending > 0 ? '$pending changes queued' : 'Offline';
      syncIcon = Icons.cloud_off_outlined;
    } else if (sync.valueOrNull?.isSyncing == true) {
      syncLabel = 'Syncing now';
      syncIcon = Icons.sync_rounded;
    } else if ((sync.valueOrNull?.pendingActions ?? 0) > 0) {
      syncLabel = '${sync.valueOrNull!.pendingActions} changes waiting';
      syncIcon = Icons.sync_problem_rounded;
    } else {
      syncLabel = 'All changes synced';
      syncIcon = Icons.sync_rounded;
    }

    return AnnotatedRegion<SystemUiOverlayStyle>(
      value: const SystemUiOverlayStyle(
        statusBarColor: AppColors.heroBackground,
        statusBarIconBrightness: Brightness.light,
        systemNavigationBarColor: AppColors.surface,
        systemNavigationBarIconBrightness: Brightness.dark,
      ),
      child: Container(
        color: AppColors.heroBackground,
        padding: EdgeInsets.fromLTRB(
          24,
          MediaQuery.viewPaddingOf(context).top + 12,
          20,
          24,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Text(
                  'INVISION',
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    color: AppColors.onHero,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 1.1,
                  ),
                ),
                const Spacer(),
                IconButton(
                  tooltip: 'Add store',
                  icon: const Icon(Icons.add_business_outlined),
                  color: AppColors.onHero,
                  onPressed: () => context.push('/stores/create'),
                ),
                Stack(
                  alignment: Alignment.center,
                  children: [
                    IconButton(
                      tooltip: 'Notifications',
                      icon: const Icon(
                        Icons.notifications_none_rounded,
                        size: 27,
                      ),
                      color: AppColors.onHero,
                      onPressed: () =>
                          ref.read(_fieldForceTabProvider.notifier).state = 1,
                    ),
                    if (unreadCount > 0)
                      Positioned(
                        top: 5,
                        right: 5,
                        child: Container(
                          constraints: const BoxConstraints(
                            minWidth: 18,
                            minHeight: 18,
                          ),
                          padding: const EdgeInsets.symmetric(horizontal: 4),
                          decoration: const BoxDecoration(
                            color: AppColors.tertiary,
                            shape: BoxShape.circle,
                          ),
                          alignment: Alignment.center,
                          child: Text(
                            unreadCount > 9 ? '9+' : '$unreadCount',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 16),
            Text(
              '$greeting, $firstName',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                color: AppColors.onHero,
                fontWeight: FontWeight.w600,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              'Today',
              style: Theme.of(context).textTheme.displayLarge?.copyWith(
                color: AppColors.onHero,
                fontSize: 48,
                height: 1.05,
              ),
            ),
            const SizedBox(height: 18),
            Row(
              children: [
                Container(
                  width: 10,
                  height: 10,
                  decoration: BoxDecoration(
                    color: isActive
                        ? const Color(0xFF67C78F)
                        : AppColors.tertiary,
                    shape: BoxShape.circle,
                  ),
                ),
                const SizedBox(width: 8),
                Text(
                  isActive ? 'On Duty' : 'Off Duty',
                  style: Theme.of(context).textTheme.titleSmall?.copyWith(
                    fontWeight: FontWeight.w700,
                    color: AppColors.onHero,
                  ),
                ),
                const SizedBox(width: 12),
                Container(
                  width: 1,
                  height: 20,
                  color: AppColors.heroMuted.withValues(alpha: 0.5),
                ),
                const SizedBox(width: 12),
                Icon(syncIcon, size: 18, color: AppColors.heroMuted),
                const SizedBox(width: 7),
                Expanded(
                  child: InkWell(
                    borderRadius: BorderRadius.circular(6),
                    onTap: () => context.push('/sync'),
                    child: Text(
                      syncLabel,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        color: AppColors.heroMuted,
                      ),
                    ),
                  ),
                ),
                IconButton(
                  tooltip: isActive ? 'End duty' : 'Start duty',
                  visualDensity: VisualDensity.compact,
                  onPressed: () async {
                    final controller = ref.read(
                      gpsTrackingControllerProvider.notifier,
                    );
                    if (!isActive) {
                      await controller.startDuty();
                      return;
                    }
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
                  },
                  icon: Icon(
                    isActive
                        ? Icons.stop_circle_outlined
                        : Icons.play_circle_outline_rounded,
                  ),
                  color: AppColors.onHero,
                ),
              ],
            ),
            if (trackingState.error != null) ...[
              const SizedBox(height: 10),
              Text(
                trackingState.error!,
                style: const TextStyle(color: Color(0xFFFFD3C2), fontSize: 12),
              ),
            ],
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
                  child: const Icon(
                    Icons.route_rounded,
                    size: 32,
                    color: AppColors.outline,
                  ),
                ),
                const SizedBox(height: 12),
                Text(
                  'No route assigned for today',
                  style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                    color: AppColors.onSurfaceVariant,
                  ),
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
        child: Text(
          'Error loading route: $e',
          style: const TextStyle(color: AppColors.error),
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
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Failed to start route: $e')));
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _checkIn(StoreVisit visit) async {
    setState(() => _loading = true);
    try {
      final expectedQrCode = visit.store?.qrCode;
      if (expectedQrCode == null || expectedQrCode.trim().isEmpty) {
        throw Exception(
          'This store has no QR code configured. Contact your manager.',
        );
      }
      final qrCode = await context.push<String>('/scanner');
      if (!mounted || qrCode == null) return;
      if (qrCode.trim() != expectedQrCode.trim()) {
        throw Exception('The scanned QR code does not match this store.');
      }

      final locationService = ref.read(locationServiceProvider);
      final position = await locationService.getCurrentPosition();

      if (position == null) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text(
                'GPS unavailable. Please enable location services and try again.',
              ),
            ),
          );
        }
        return;
      }

      final repo = ref.read(routeRepositoryProvider);
      final checkedVisit = await repo.checkIn(
        visit.id,
        latitude: position.latitude,
        longitude: position.longitude,
        qrCode: qrCode.trim(),
      );
      ref.invalidate(myRouteTodayProvider);
      if (!mounted) return;
      await context.push('/visits/${checkedVisit.id}/capture');
      if (!mounted) return;
      ref.invalidate(myRouteTodayProvider);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Check-in failed: $e')));
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
            const SnackBar(
              content: Text(
                'GPS unavailable. Please enable location services and try again.',
              ),
            ),
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
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Check-out failed: $e')));
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
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Skip failed: $e')));
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
      'https://www.google.com/maps/dir/?api=1&destination=$lat,$lng&travelmode=driving',
    );
    launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  @override
  Widget build(BuildContext context) {
    final instance = widget.instance;
    final visits = [...instance.visits]
      ..sort((a, b) => a.visitOrder.compareTo(b.visitOrder));
    final currentVisit = visits.cast<StoreVisit?>().firstWhere(
      (visit) => visit?.status == VisitStatus.checkedIn,
      orElse: () => visits.cast<StoreVisit?>().firstWhere(
        (visit) => visit?.status == VisitStatus.pending,
        orElse: () => null,
      ),
    );
    final remaining = visits
        .where(
          (visit) =>
              visit.status == VisitStatus.pending ||
              visit.status == VisitStatus.checkedIn,
        )
        .length;
    final notStarted = instance.status == RouteStatus.published;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            Expanded(
              child: Text(
                'Today’s route',
                style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                  color: AppColors.onSurface,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ),
            Text(
              '${instance.totalVisits} stores · $remaining remaining',
              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                color: AppColors.onSurfaceVariant,
              ),
            ),
          ],
        ),
        const SizedBox(height: 4),
        Row(
          children: [
            Expanded(
              child: Text(
                instance.routePlanName ?? 'Route #${instance.routePlanId}',
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  color: AppColors.onSurfaceVariant,
                ),
              ),
            ),
            _StatusBadge(status: instance.status),
          ],
        ),
        if (notStarted) ...[
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: FilledButton.icon(
              onPressed: _loading ? null : _startRoute,
              icon: _loading
                  ? const SizedBox.square(
                      dimension: 18,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                      ),
                    )
                  : const Icon(Icons.play_arrow_rounded),
              label: const Text('Start route'),
            ),
          ),
        ],
        if (currentVisit != null) ...[
          const SizedBox(height: 16),
          _CurrentVisitCard(
            visit: currentVisit,
            totalVisits: instance.totalVisits,
            isCheckedIn: currentVisit.status == VisitStatus.checkedIn,
            isLoading: _loading,
            onContinue: () =>
                context.push('/visits/${currentVisit.id}/capture'),
            onCheckIn: () => _checkIn(currentVisit),
            onNavigate: () => _navigateToStore(currentVisit),
            onCheckOut: () => _checkOut(currentVisit),
            onPos: () => context.push(
              '/pos/create/${currentVisit.storeId}',
              extra: {
                'storeName':
                    currentVisit.store?.name ??
                    'Store #${currentVisit.storeId}',
              },
            ),
            onOrder: () => context.push(
              '/sales/create',
              extra: {
                'storeId': currentVisit.storeId,
                'storeName':
                    currentVisit.store?.name ??
                    'Store #${currentVisit.storeId}',
              },
            ),
            onSkip: () => _skipVisit(currentVisit),
          ),
        ] else if (visits.isNotEmpty && remaining == 0) ...[
          const SizedBox(height: 16),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppColors.secondaryContainer,
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Row(
              children: [
                Icon(
                  Icons.check_circle_outline_rounded,
                  color: AppColors.secondary,
                ),
                SizedBox(width: 10),
                Expanded(
                  child: Text('No remaining visits on today’s route.'),
                ),
              ],
            ),
          ),
        ],
        if (visits.isNotEmpty) ...[
          const SizedBox(height: 18),
          for (var i = 0; i < visits.length; i++)
            _RouteTimelineItem(
              visit: visits[i],
              isLast: i == visits.length - 1,
              isCurrent: visits[i].id == currentVisit?.id,
              onOpen: () => context.push('/stores/${visits[i].storeId}'),
              onCheckIn: () => _checkIn(visits[i]),
              onNavigate: () => _navigateToStore(visits[i]),
              onSkip: () => _skipVisit(visits[i]),
            ),
        ],
      ],
    );
  }
}

class _CurrentVisitCard extends StatelessWidget {
  const _CurrentVisitCard({
    required this.visit,
    required this.totalVisits,
    required this.isCheckedIn,
    required this.isLoading,
    required this.onContinue,
    required this.onCheckIn,
    required this.onNavigate,
    required this.onCheckOut,
    required this.onPos,
    required this.onOrder,
    required this.onSkip,
  });

  final StoreVisit visit;
  final int totalVisits;
  final bool isCheckedIn;
  final bool isLoading;
  final VoidCallback onContinue;
  final VoidCallback onCheckIn;
  final VoidCallback onNavigate;
  final VoidCallback onCheckOut;
  final VoidCallback onPos;
  final VoidCallback onOrder;
  final VoidCallback onSkip;

  @override
  Widget build(BuildContext context) {
    final storeName = visit.store?.name ?? 'Store #${visit.storeId}';
    final location = visit.store?.areaName ?? visit.store?.address;
    final checkInTime = _visitTime(visit.checkedInAt);

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: AppColors.outlineVariant.withValues(alpha: 0.65),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  isCheckedIn ? 'ACTIVE VISIT' : 'UP NEXT',
                  style: Theme.of(context).textTheme.labelLarge?.copyWith(
                    color: AppColors.tertiary,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 1.1,
                  ),
                ),
              ),
              if (isCheckedIn)
                _VisitStatusChip(status: visit.status)
              else
                Text(
                  '${visit.visitOrder} OF $totalVisits',
                  style: Theme.of(context).textTheme.labelMedium?.copyWith(
                    color: AppColors.onSurfaceVariant,
                    letterSpacing: 0.6,
                  ),
                ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            storeName,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: Theme.of(context).textTheme.headlineSmall?.copyWith(
              color: AppColors.onSurface,
              fontWeight: FontWeight.w800,
            ),
          ),
          if (location != null && location.isNotEmpty) ...[
            const SizedBox(height: 5),
            Row(
              children: [
                const Icon(
                  Icons.location_on_rounded,
                  size: 18,
                  color: AppColors.onSurfaceVariant,
                ),
                const SizedBox(width: 5),
                Expanded(
                  child: Text(
                    location,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: AppColors.onSurfaceVariant,
                    ),
                  ),
                ),
              ],
            ),
          ],
          if (checkInTime != null) ...[
            const SizedBox(height: 10),
            Row(
              children: [
                const Icon(
                  Icons.radio_button_checked_rounded,
                  size: 17,
                  color: AppColors.tertiary,
                ),
                const SizedBox(width: 7),
                Text(
                  'Checked in · $checkInTime',
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: AppColors.onSurfaceVariant,
                  ),
                ),
              ],
            ),
          ],
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: FilledButton.icon(
                  onPressed: isLoading
                      ? null
                      : (isCheckedIn ? onContinue : onCheckIn),
                  style: FilledButton.styleFrom(
                    backgroundColor: AppColors.tertiary,
                    foregroundColor: Colors.white,
                    minimumSize: const Size.fromHeight(54),
                  ),
                  icon: Icon(
                    isCheckedIn
                        ? Icons.arrow_forward_rounded
                        : Icons.qr_code_scanner_rounded,
                  ),
                  label: Text(isCheckedIn ? 'Continue visit' : 'Check in'),
                ),
              ),
              const SizedBox(width: 4),
              PopupMenuButton<String>(
                tooltip: 'More visit actions',
                enabled: !isLoading,
                onSelected: (action) {
                  switch (action) {
                    case 'navigate':
                      onNavigate();
                    case 'pos':
                      onPos();
                    case 'order':
                      onOrder();
                    case 'checkout':
                      onCheckOut();
                    case 'skip':
                      onSkip();
                  }
                },
                itemBuilder: (context) => [
                  if (visit.store?.gpsLatitude != null)
                    const PopupMenuItem(
                      value: 'navigate',
                      child: Text('Navigate to store'),
                    ),
                  if (isCheckedIn) ...[
                    const PopupMenuItem(
                      value: 'pos',
                      child: Text('Record POS sale'),
                    ),
                    const PopupMenuItem(
                      value: 'order',
                      child: Text('Create order'),
                    ),
                    const PopupMenuItem(
                      value: 'checkout',
                      child: Text('Check out'),
                    ),
                  ] else
                    const PopupMenuItem(
                      value: 'skip',
                      child: Text('Skip visit'),
                    ),
                ],
                child: const SizedBox(
                  width: 48,
                  height: 48,
                  child: Icon(
                    Icons.more_horiz_rounded,
                    color: AppColors.onSurfaceVariant,
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _RouteTimelineItem extends StatelessWidget {
  const _RouteTimelineItem({
    required this.visit,
    required this.isLast,
    required this.isCurrent,
    required this.onOpen,
    required this.onCheckIn,
    required this.onNavigate,
    required this.onSkip,
  });

  final StoreVisit visit;
  final bool isLast;
  final bool isCurrent;
  final VoidCallback onOpen;
  final VoidCallback onCheckIn;
  final VoidCallback onNavigate;
  final VoidCallback onSkip;

  @override
  Widget build(BuildContext context) {
    final isCompleted = visit.status == VisitStatus.completed;
    final isSkipped = visit.status == VisitStatus.skipped;
    final markerColor = isCompleted
        ? AppColors.secondary
        : isCurrent
        ? AppColors.tertiary
        : AppColors.outline;
    final storeName = visit.store?.name ?? 'Store #${visit.storeId}';
    final location = visit.store?.areaName ?? visit.store?.address;
    final time = _visitTime(visit.checkedOutAt ?? visit.checkedInAt);

    return Row(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        SizedBox(
          width: 42,
          child: Column(
            children: [
              Container(
                width: 38,
                height: 38,
                decoration: BoxDecoration(
                  color: markerColor,
                  shape: BoxShape.circle,
                ),
                alignment: Alignment.center,
                child: isCompleted
                    ? const Icon(
                        Icons.check_rounded,
                        color: Colors.white,
                        size: 21,
                      )
                    : isSkipped
                    ? const Icon(
                        Icons.remove_rounded,
                        color: Colors.white,
                        size: 21,
                      )
                    : Text(
                        '${visit.visitOrder}',
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
              ),
              if (!isLast)
                Expanded(
                  child: Container(
                    width: 2,
                    margin: const EdgeInsets.symmetric(vertical: 2),
                    color: isCompleted
                        ? AppColors.secondary.withValues(alpha: 0.65)
                        : AppColors.outlineVariant,
                  ),
                ),
            ],
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Padding(
            padding: EdgeInsets.only(bottom: isLast ? 0 : 14),
            child: InkWell(
              onTap: onOpen,
              borderRadius: BorderRadius.circular(10),
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 4),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            storeName,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: Theme.of(context).textTheme.titleMedium
                                ?.copyWith(
                                  color: AppColors.onSurface,
                                  fontWeight: FontWeight.w800,
                                ),
                          ),
                          if (location != null && location.isNotEmpty) ...[
                            const SizedBox(height: 2),
                            Text(
                              location,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: Theme.of(context).textTheme.bodySmall
                                  ?.copyWith(color: AppColors.onSurfaceVariant),
                            ),
                          ],
                          if (time != null) ...[
                            const SizedBox(height: 4),
                            Text(
                              isCompleted
                                  ? 'Completed · $time'
                                  : 'Checked in · $time',
                              style: Theme.of(context).textTheme.bodySmall
                                  ?.copyWith(color: AppColors.onSurfaceVariant),
                            ),
                          ],
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        _VisitStatusChip(status: visit.status),
                        if (!isCompleted && !isSkipped)
                          PopupMenuButton<String>(
                            tooltip: 'Visit actions',
                            onSelected: (action) {
                              if (action == 'checkin') onCheckIn();
                              if (action == 'navigate') onNavigate();
                              if (action == 'skip') onSkip();
                            },
                            itemBuilder: (context) => [
                              if (visit.status == VisitStatus.pending)
                                const PopupMenuItem(
                                  value: 'checkin',
                                  child: Text('Check in'),
                                ),
                              if (visit.store?.gpsLatitude != null)
                                const PopupMenuItem(
                                  value: 'navigate',
                                  child: Text('Navigate'),
                                ),
                              if (visit.status == VisitStatus.pending)
                                const PopupMenuItem(
                                  value: 'skip',
                                  child: Text('Skip visit'),
                                ),
                            ],
                            child: const SizedBox(
                              width: 48,
                              height: 48,
                              child: Icon(
                                Icons.more_horiz_rounded,
                                color: AppColors.onSurfaceVariant,
                              ),
                            ),
                          ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ],
    );
  }
}

String? _visitTime(String? value) {
  if (value == null) return null;
  try {
    final date = DateTime.parse(value).toLocal();
    final hour = date.hour % 12 == 0 ? 12 : date.hour % 12;
    final minute = date.minute.toString().padLeft(2, '0');
    final period = date.hour >= 12 ? 'PM' : 'AM';
    return '$hour:$minute $period';
  } catch (_) {
    return null;
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
                      child: const Icon(
                        Icons.notifications_none_rounded,
                        size: 32,
                        color: AppColors.outline,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      'No notifications',
                      style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                        color: AppColors.onSurfaceVariant,
                      ),
                    ),
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
                                  width: 3,
                                ),
                              ),
                      ),
                      child: ListTile(
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: 14,
                          vertical: 4,
                        ),
                        leading: Container(
                          width: 36,
                          height: 36,
                          decoration: BoxDecoration(
                            color: n.isRead
                                ? AppColors.surfaceContainerHigh
                                : AppColors.primaryContainer.withValues(
                                    alpha: 0.2,
                                  ),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Icon(
                            n.type.icon,
                            size: 18,
                            color: n.isRead
                                ? AppColors.outline
                                : AppColors.primaryContainer,
                          ),
                        ),
                        title: Text(
                          n.title,
                          style: Theme.of(context).textTheme.titleSmall
                              ?.copyWith(
                                fontWeight: n.isRead
                                    ? FontWeight.w500
                                    : FontWeight.w700,
                                color: AppColors.onSurface,
                              ),
                        ),
                        subtitle: n.body != null
                            ? Text(
                                n.body!,
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                                style: Theme.of(context).textTheme.bodySmall
                                    ?.copyWith(
                                      color: AppColors.onSurfaceVariant,
                                    ),
                              )
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
                            final repo = ref.read(
                              notificationsRepositoryProvider,
                            );
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
        loading: () => const Center(
          child: CircularProgressIndicator(color: AppColors.primary),
        ),
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
        backgroundColor: AppColors.surface.withValues(alpha: 0.9),
        elevation: 0,
        title: Text(
          'Profile',
          style: tt.titleLarge?.copyWith(color: AppColors.onSurface),
        ),
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
                      colors: [AppColors.primary, AppColors.primaryContainer],
                    ),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  alignment: Alignment.center,
                  child: Text(
                    _initials(user?.name ?? ''),
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 28,
                      fontWeight: FontWeight.w800,
                      fontFamily: 'Manrope',
                    ),
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
                  style: tt.bodyMedium?.copyWith(
                    color: AppColors.onSurfaceVariant,
                  ),
                ),
                if (user?.role != null) ...[
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 6,
                    ),
                    decoration: BoxDecoration(
                      color: AppColors.secondaryContainer,
                      borderRadius: BorderRadius.circular(100),
                    ),
                    child: Text(
                      _roleLabel(user!.role!),
                      style: tt.labelMedium?.copyWith(
                        color: AppColors.onSecondaryContainer,
                      ),
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
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 16,
                vertical: 4,
              ),
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
                style: tt.bodySmall?.copyWith(
                  color: AppColors.onSurfaceVariant,
                ),
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
                _ProfileTile(
                  icon: Icons.language_rounded,
                  label: 'Language',
                  onTap: () => context.push('/language'),
                ),
                Divider(
                  color: AppColors.outlineVariant.withValues(alpha: 0.5),
                  height: 1,
                  indent: 54,
                ),
                _ProfileTile(
                  icon: Icons.sync_rounded,
                  label: 'Sync Status',
                  onTap: () => context.push('/sync'),
                ),
                Divider(
                  color: AppColors.outlineVariant.withValues(alpha: 0.5),
                  height: 1,
                  indent: 54,
                ),
                _ProfileTile(
                  icon: Icons.checklist_rounded,
                  label: 'My Assigned Tasks',
                  onTap: () => context.push('/assigned-tasks'),
                ),
                Divider(
                  color: AppColors.outlineVariant.withValues(alpha: 0.5),
                  height: 1,
                  indent: 54,
                ),
                _ProfileTile(
                  icon: Icons.receipt_long_rounded,
                  label: 'My Orders',
                  onTap: () => context.push('/my-orders'),
                ),
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
                      style: FilledButton.styleFrom(
                        backgroundColor: AppColors.error,
                      ),
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
                  const Icon(
                    Icons.logout_rounded,
                    color: AppColors.onErrorContainer,
                    size: 18,
                  ),
                  const SizedBox(width: 8),
                  Text(
                    'Logout',
                    style: tt.labelLarge?.copyWith(
                      color: AppColors.onErrorContainer,
                    ),
                  ),
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
      RouteStatus.inProgress => (
        AppColors.primaryContainer,
        AppColors.primaryContainer.withValues(alpha: 0.15),
      ),
      RouteStatus.completed => (
        AppColors.secondary,
        AppColors.secondaryContainer,
      ),
      RouteStatus.published => (
        AppColors.tertiary,
        AppColors.tertiaryContainer.withValues(alpha: 0.25),
      ),
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
          color: color,
          fontSize: 11,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }
}

class _ProfileTile extends StatelessWidget {
  const _ProfileTile({
    required this.icon,
    required this.label,
    required this.onTap,
  });
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
      title: Text(
        label,
        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
          color: AppColors.onSurface,
          fontWeight: FontWeight.w500,
        ),
      ),
      trailing: const Icon(
        Icons.chevron_right_rounded,
        color: AppColors.outline,
        size: 18,
      ),
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
      VisitStatus.pending => (
        AppColors.onSurfaceVariant,
        AppColors.surfaceContainerHigh,
        Icons.circle_outlined,
      ),
      VisitStatus.checkedIn => (
        AppColors.tertiary,
        AppColors.tertiaryContainer,
        Icons.login_rounded,
      ),
      VisitStatus.completed => (
        AppColors.secondary,
        AppColors.secondaryContainer,
        Icons.check_circle_rounded,
      ),
      VisitStatus.skipped => (
        AppColors.error,
        AppColors.errorContainer,
        Icons.skip_next_rounded,
      ),
    };
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(100),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 11, color: color),
          const SizedBox(width: 3),
          Text(
            status.label,
            style: TextStyle(
              color: color,
              fontSize: 10,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }
}
