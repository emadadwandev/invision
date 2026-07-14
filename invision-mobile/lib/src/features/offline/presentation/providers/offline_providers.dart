import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/services/offline_database.dart';
import '../../../../core/services/sync_engine.dart';

// ---------------------------------------------------------------------------
// Connectivity
// ---------------------------------------------------------------------------

/// Stream of connectivity changes.
final connectivityProvider = StreamProvider<List<ConnectivityResult>>((ref) {
  return Connectivity().onConnectivityChanged;
});

/// Whether the device is currently online.
final isOnlineProvider = Provider<bool>((ref) {
  final connectivity = ref.watch(connectivityProvider);
  return connectivity.when(
    data: (results) => results.any((r) => r != ConnectivityResult.none),
    loading: () => true, // assume online until we know
    error: (_, __) => true,
  );
});

// ---------------------------------------------------------------------------
// Offline Database
// ---------------------------------------------------------------------------

final offlineDatabaseProvider = Provider<OfflineDatabase>((ref) {
  return OfflineDatabase.instance;
});

// ---------------------------------------------------------------------------
// Sync Engine
// ---------------------------------------------------------------------------

/// Device ID — in production, use a persisted unique ID.
/// For now, a simple constant per install.
final deviceIdProvider = Provider<String>((ref) {
  return 'flutter-device-001'; // TODO: persist via shared_preferences
});

final syncEngineProvider = Provider<SyncEngine>((ref) {
  final deviceId = ref.watch(deviceIdProvider);
  final engine = SyncEngine(deviceId: deviceId);
  engine.start();
  ref.onDispose(engine.dispose);
  return engine;
});

/// Stream of sync status updates.
final syncStatusProvider = StreamProvider<SyncStatus>((ref) {
  final engine = ref.watch(syncEngineProvider);
  return engine.statusStream;
});

/// Current pending action count.
final pendingActionsCountProvider = FutureProvider<int>((ref) async {
  // Re-evaluate when sync status changes
  ref.watch(syncStatusProvider);
  final db = ref.watch(offlineDatabaseProvider);
  return db.getPendingCount();
});
