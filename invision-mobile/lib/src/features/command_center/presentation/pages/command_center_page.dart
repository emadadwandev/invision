import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:flutter_map_cancellable_tile_provider/flutter_map_cancellable_tile_provider.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:latlong2/latlong.dart';

import '../../data/models/field_force_position.dart';
import '../../data/models/store_map_item.dart';
import '../../../realtime/presentation/providers/realtime_providers.dart';
import '../providers/command_center_providers.dart';
import '../../../../core/theme/app_theme.dart';

class CommandCenterPage extends ConsumerStatefulWidget {
  const CommandCenterPage({super.key});

  @override
  ConsumerState<CommandCenterPage> createState() => _CommandCenterPageState();
}

class _CommandCenterPageState extends ConsumerState<CommandCenterPage> {
  final MapController _mapController = MapController();
  Timer? _refreshTimer;
  bool _showStores = true;
  bool _showFieldForce = true;

  @override
  void initState() {
    super.initState();
    // Auto-refresh field force positions every 30 seconds as fallback
    _refreshTimer = Timer.periodic(const Duration(seconds: 30), (_) {
      ref.invalidate(fieldForcePositionsProvider);
    });
    // Connect WebSocket for real-time GPS updates
    _connectWebSocket();
  }

  void _connectWebSocket() {
    final wsService = ref.read(webSocketServiceProvider);
    if (!wsService.isConnected) {
      wsService.connect();
    }
    // Subscribe to tenant tracking channel (tenant 1 for now)
    wsService.subscribePrivate('tenant.1.tracking');
    wsService.on('tenant.1.tracking', 'gps.position.updated', (event) {
      // When a real-time GPS update arrives, refresh the field force markers
      ref.invalidate(fieldForcePositionsProvider);
    });
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    _mapController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final statsAsync = ref.watch(commandCenterStatsProvider);
    final fieldForceAsync = ref.watch(fieldForcePositionsProvider);
    final storesAsync = ref.watch(storeMapDataProvider);

    return Scaffold(
      appBar: AppBar(
        title: Text('Command Center',
            style: Theme.of(context).textTheme.headlineMedium
                ?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
        actions: [
          // WebSocket connection indicator
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 4),
            child: ref.watch(wsConnectionProvider).when(
                  data: (connected) => Icon(
                    Icons.wifi_rounded,
                    color: connected ? AppColors.secondary : AppColors.outline,
                    size: 18,
                  ),
                  loading: () =>
                      const Icon(Icons.wifi_rounded, color: AppColors.outline, size: 18),
                  error: (_, __) =>
                      const Icon(Icons.wifi_off_rounded, color: AppColors.error, size: 18),
                ),
          ),
          IconButton(
            icon: Icon(
              _showFieldForce ? Icons.people_rounded : Icons.people_outline,
              color: AppColors.onSurface,
            ),
            tooltip: 'Toggle Field Force',
            onPressed: () => setState(() => _showFieldForce = !_showFieldForce),
          ),
          IconButton(
            icon: Icon(_showStores ? Icons.store_rounded : Icons.store_outlined,
                color: AppColors.onSurface),
            tooltip: 'Toggle Stores',
            onPressed: () => setState(() => _showStores = !_showStores),
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: AppColors.onSurface),
            tooltip: 'Refresh',
            onPressed: () {
              ref.invalidate(commandCenterStatsProvider);
              ref.invalidate(fieldForcePositionsProvider);
              ref.invalidate(storeMapDataProvider);
            },
          ),
        ],
      ),
      body: Column(
        children: [
          // Stats bar
          statsAsync.when(
            data: (stats) => _StatsBar(stats: stats),
            loading: () => const SizedBox(
              height: 60,
              child: Center(
                  child: CircularProgressIndicator(color: AppColors.primary)),
            ),
            error: (_, __) => const SizedBox.shrink(),
          ),
          // Map
          Expanded(
            child: FlutterMap(
              mapController: _mapController,
              options: MapOptions(
                initialCenter: const LatLng(33.8938, 35.5018),
                initialZoom: 13,
              ),
              children: [
                TileLayer(
                  urlTemplate:
                      'https://api.mapbox.com/styles/v1/mapbox/streets-v12/tiles/{z}/{x}/{y}@2x?access_token={accessToken}',
                  additionalOptions: const {
                    'accessToken': String.fromEnvironment(
                      'MAPBOX_TOKEN',
                      defaultValue: '',
                    ),
                  },
                  userAgentPackageName: 'com.invision.mobile',
                  tileProvider: CancellableNetworkTileProvider(),
                  fallbackUrl:
                      'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                ),
                // Store markers
                if (_showStores)
                  storesAsync.when(
                    data: (stores) => MarkerLayer(
                      markers: stores
                          .map((store) => _buildStoreMarker(store))
                          .toList(),
                    ),
                    loading: () => const MarkerLayer(markers: []),
                    error: (_, __) => const MarkerLayer(markers: []),
                  ),
                // Field force markers
                if (_showFieldForce)
                  fieldForceAsync.when(
                    data: (positions) => MarkerLayer(
                      markers: positions
                          .where((p) => p.hasLocation)
                          .map((p) => _buildUserMarker(p))
                          .toList(),
                    ),
                    loading: () => const MarkerLayer(markers: []),
                    error: (_, __) => const MarkerLayer(markers: []),
                  ),
              ],
            ),
          ),
        ],
      ),
      // Bottom sheet: field force list
      bottomSheet: DraggableScrollableSheet(
        initialChildSize: 0.15,
        minChildSize: 0.08,
        maxChildSize: 0.5,
        expand: false,
        builder: (context, scrollController) => Container(
          decoration: BoxDecoration(
            color: Theme.of(context).colorScheme.surface,
            borderRadius: const BorderRadius.vertical(
              top: Radius.circular(16),
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.1),
                blurRadius: 10,
              ),
            ],
          ),
          child: fieldForceAsync.when(
            data: (positions) => _FieldForceList(
              positions: positions,
              scrollController: scrollController,
              onTapUser: _focusOnUser,
            ),
            loading: () => const Center(child: CircularProgressIndicator()),
            error: (e, _) => Center(
              child: Text('Error: $e'),
            ),
          ),
        ),
      ),
    );
  }

  Marker _buildStoreMarker(StoreMapItem store) {
    return Marker(
      point: LatLng(store.latitude, store.longitude),
      width: 40,
      height: 40,
      child: GestureDetector(
        onTap: () => _showStorePopup(store),
        child: const Icon(Icons.storefront_rounded,
            color: AppColors.primary, size: 32),
      ),
    );
  }

  Marker _buildUserMarker(FieldForcePosition user) {
    return Marker(
      point: LatLng(user.latitude!, user.longitude!),
      width: 36,
      height: 36,
      child: GestureDetector(
        onTap: () => _showUserPopup(user),
        child: Container(
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: user.isOnline ? AppColors.secondary : AppColors.outline,
            border: Border.all(color: Colors.white, width: 2),
            boxShadow: [
              if (user.isOnline)
                BoxShadow(
                  color: AppColors.secondary.withOpacity(0.4),
                  blurRadius: 8,
                  spreadRadius: 2,
                ),
            ],
          ),
          child: const Icon(Icons.person, color: Colors.white, size: 20),
        ),
      ),
    );
  }

  void _focusOnUser(FieldForcePosition user) {
    if (user.hasLocation) {
      _mapController.move(LatLng(user.latitude!, user.longitude!), 16);
    }
  }

  void _showStorePopup(StoreMapItem store) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (_) => _StoreDetailSheet(store: store),
    );
  }

  void _showUserPopup(FieldForcePosition user) {
    showModalBottomSheet(
      context: context,
      builder: (_) => _UserDetailSheet(user: user),
    );
  }
}

// --------------- Stats Bar ---------------

class _StatsBar extends StatelessWidget {
  const _StatsBar({required this.stats});

  final dynamic stats;

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 60,
      color: Theme.of(context).colorScheme.surfaceContainerLow,
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceEvenly,
        children: [
          _StatChip(
            label: 'Online',
            value: '${stats.onlineCount}/${stats.totalFieldForce}',
            color: AppColors.secondary,
          ),
          _StatChip(
            label: 'Routes',
            value: '${stats.activeRoutes}',
            color: AppColors.primaryContainer,
          ),
          _StatChip(
            label: 'Stores',
            value: '${stats.totalStores}',
            color: AppColors.primary,
          ),
          _StatChip(
            label: 'Orders',
            value: '${stats.todayOrders}',
            color: AppColors.tertiary,
          ),
        ],
      ),
    );
  }
}

class _StatChip extends StatelessWidget {
  const _StatChip({
    required this.label,
    required this.value,
    required this.color,
  });

  final String label;
  final String value;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Text(
          value,
          style: TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: 16,
            color: color,
          ),
        ),
        Text(
          label,
          style: Theme.of(context).textTheme.bodySmall,
        ),
      ],
    );
  }
}

// --------------- Field Force List ---------------

class _FieldForceList extends StatelessWidget {
  const _FieldForceList({
    required this.positions,
    required this.scrollController,
    required this.onTapUser,
  });

  final List<FieldForcePosition> positions;
  final ScrollController scrollController;
  final ValueChanged<FieldForcePosition> onTapUser;

  @override
  Widget build(BuildContext context) {
    final online = positions.where((p) => p.isOnline).toList();
    final offline = positions.where((p) => !p.isOnline).toList();
    final sorted = [...online, ...offline];

    return Column(
      children: [
        // Handle
        Container(
          margin: const EdgeInsets.only(top: 8),
          width: 40, height: 4,
          decoration: BoxDecoration(
            color: AppColors.outlineVariant,
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        Padding(
          padding: const EdgeInsets.all(12),
          child: Text(
            'Field Force (${online.length} online)',
            style: Theme.of(context).textTheme.titleSmall
                ?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w700),
          ),
        ),
        Expanded(
          child: ListView.builder(
            controller: scrollController,
            itemCount: sorted.length,
            itemBuilder: (context, index) {
              final user = sorted[index];
              return ListTile(
                leading: CircleAvatar(
                  backgroundColor:
                      user.isOnline ? AppColors.secondary : AppColors.outline,
                  child:
                      const Icon(Icons.person, color: Colors.white, size: 20),
                ),
                title: Text(user.name,
                    style: const TextStyle(color: AppColors.onSurface)),
                subtitle: Text(user.roleLabel,
                    style: const TextStyle(color: AppColors.onSurfaceVariant)),
                trailing: user.isOnline
                    ? Text(
                        '${user.speedKmh?.toStringAsFixed(1) ?? '0'} km/h',
                        style: const TextStyle(
                            fontSize: 12, color: AppColors.onSurface),
                      )
                    : const Text(
                        'Offline',
                        style: TextStyle(
                            fontSize: 12, color: AppColors.outline),
                      ),
                onTap: () => onTapUser(user),
              );
            },
          ),
        ),
      ],
    );
  }
}

// --------------- Store Detail Sheet ---------------

class _StoreDetailSheet extends StatelessWidget {
  const _StoreDetailSheet({required this.store});

  final StoreMapItem store;

  @override
  Widget build(BuildContext context) {
    return DraggableScrollableSheet(
      initialChildSize: 0.45,
      minChildSize: 0.3,
      maxChildSize: 0.8,
      expand: false,
      builder: (context, scrollController) => ListView(
        controller: scrollController,
        padding: const EdgeInsets.all(20),
        children: [
          Center(
            child: Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: AppColors.outlineVariant,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Text(
            store.name,
            style: Theme.of(context).textTheme.titleLarge
                ?.copyWith(color: AppColors.onSurface),
          ),
          Text(
            '${store.code} • ${store.category ?? ''} • ${store.rank ?? ''}',
            style: Theme.of(context).textTheme.bodySmall
                ?.copyWith(color: AppColors.onSurfaceVariant),
          ),
          if (store.address != null) ...[
            const SizedBox(height: 4),
            Text(store.address!,
                style: Theme.of(context).textTheme.bodySmall
                    ?.copyWith(color: AppColors.onSurfaceVariant)),
          ],
          const Divider(height: 24),
          // Sales
          _InfoRow(label: 'Orders', value: '${store.sales.orderCount}'),
          _InfoRow(
            label: 'Total Sales',
            value: '\$${store.sales.totalSales.toStringAsFixed(2)}',
          ),
          const Divider(height: 16),
          // Inventory
          _InfoRow(
            label: 'Products',
            value: '${store.inventory.productCount}',
          ),
          _InfoRow(label: 'Total Stock', value: '${store.inventory.totalStock}'),
          if (store.credit != null) ...[
            const Divider(height: 16),
            _InfoRow(
              label: 'Credit Limit',
              value: '\$${store.credit!.creditLimit.toStringAsFixed(2)}',
            ),
            _InfoRow(
              label: 'Balance',
              value: '\$${store.credit!.currentBalance.toStringAsFixed(2)}',
            ),
            _InfoRow(
              label: 'Available',
              value: '\$${store.credit!.availableCredit.toStringAsFixed(2)}',
            ),
          ],
        ],
      ),
    );
  }
}

// --------------- User Detail Sheet ---------------

class _UserDetailSheet extends StatelessWidget {
  const _UserDetailSheet({required this.user});

  final FieldForcePosition user;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(20),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(
            child: Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: AppColors.outlineVariant,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              CircleAvatar(
                backgroundColor:
                    user.isOnline ? AppColors.secondary : AppColors.outline,
                radius: 24,
                child: const Icon(Icons.person, color: Colors.white, size: 28),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      user.name,
                      style: Theme.of(context).textTheme.titleMedium
                          ?.copyWith(color: AppColors.onSurface),
                    ),
                    Text(
                      user.roleLabel,
                      style: Theme.of(context).textTheme.bodySmall
                          ?.copyWith(color: AppColors.onSurfaceVariant),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: user.isOnline
                      ? AppColors.secondaryContainer
                      : AppColors.surfaceContainerHigh,
                  borderRadius: BorderRadius.circular(100),
                ),
                child: Text(
                  user.isOnline ? 'Online' : 'Offline',
                  style: TextStyle(
                    color: user.isOnline
                        ? AppColors.secondary
                        : AppColors.onSurfaceVariant,
                    fontWeight: FontWeight.w600,
                    fontSize: 12,
                  ),
                ),
              ),
            ],
          ),
          const Divider(height: 24),
          if (user.speedKmh != null)
            _InfoRow(
              label: 'Speed',
              value: '${user.speedKmh!.toStringAsFixed(1)} km/h',
            ),
          if (user.lastSeen != null)
            _InfoRow(label: 'Last Seen', value: user.lastSeen!),
          const SizedBox(height: 16),
        ],
      ),
    );
  }
}

// --------------- Shared Widgets ---------------

class _InfoRow extends StatelessWidget {
  const _InfoRow({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label,
              style: const TextStyle(color: AppColors.onSurfaceVariant)),
          Text(value,
              style: const TextStyle(
                  fontWeight: FontWeight.w600, color: AppColors.onSurface)),
        ],
      ),
    );
  }
}
