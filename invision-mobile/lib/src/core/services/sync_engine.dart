import 'dart:async';
import 'dart:convert';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:uuid/uuid.dart';

import '../network/api_client.dart';
import '../constants/app_constants.dart';
import 'offline_database.dart';

/// Manages bidirectional sync between local SQLite and the server.
class SyncEngine {
  SyncEngine({
    required this.deviceId,
    ApiClient? apiClient,
  }) : _apiClient = apiClient ?? ApiClient();

  final String deviceId;
  final ApiClient _apiClient;
  final OfflineDatabase _db = OfflineDatabase.instance;
  final Uuid _uuid = const Uuid();

  StreamSubscription<List<ConnectivityResult>>? _connectivitySub;
  Timer? _syncTimer;
  bool _isSyncing = false;
  bool _isOnline = true;

  final _statusController = StreamController<SyncStatus>.broadcast();
  Stream<SyncStatus> get statusStream => _statusController.stream;

  /// Start monitoring connectivity and auto-syncing.
  void start() {
    _connectivitySub = Connectivity().onConnectivityChanged.listen((results) {
      final online = results.any(
        (r) => r != ConnectivityResult.none,
      );

      if (online && !_isOnline) {
        // Just came back online — trigger immediate sync
        _isOnline = true;
        syncNow();
      }
      _isOnline = online;
    });

    // Periodic sync every 60 seconds
    _syncTimer = Timer.periodic(
      const Duration(seconds: 60),
      (_) {
        if (_isOnline) syncNow();
      },
    );
  }

  /// Stop monitoring and syncing.
  void stop() {
    _connectivitySub?.cancel();
    _connectivitySub = null;
    _syncTimer?.cancel();
    _syncTimer = null;
  }

  /// Whether the device is currently online.
  bool get isOnline => _isOnline;

  /// Enqueue an offline action.
  Future<String> enqueue({
    required String entityType,
    required String action,
    required Map<String, dynamic> payload,
  }) async {
    final clientId = _uuid.v4();
    await _db.enqueueAction(
      clientId: clientId,
      entityType: entityType,
      action: action,
      payload: jsonEncode(payload),
      timestamp: DateTime.now().toIso8601String(),
    );

    _emitStatus();

    // If online, try to sync immediately
    if (_isOnline) {
      syncNow();
    }

    return clientId;
  }

  /// Trigger an immediate sync cycle: push pending → pull changes.
  Future<void> syncNow() async {
    if (_isSyncing || !_isOnline) return;
    _isSyncing = true;
    _emitStatus();

    try {
      // 1. Push pending offline actions
      await _pushPendingActions();

      // 2. Push buffered GPS logs
      await _pushBufferedGpsLogs();

      // 3. Pull remote changes
      await _pullChanges();

      // 4. Clean up synced items
      await _db.clearSyncedActions();
      await _db.clearSyncedGpsLogs();
    } catch (_) {
      // Sync failed — will retry on next cycle
    } finally {
      _isSyncing = false;
      _emitStatus();
    }
  }

  Future<void> _pushPendingActions() async {
    final pending = await _db.getPendingActions(limit: 50);
    if (pending.isEmpty) return;

    final actions = pending.map((row) {
      return {
        'client_id': row['client_id'],
        'entity_type': row['entity_type'],
        'action': row['action'],
        'payload': jsonDecode(row['payload'] as String),
        'timestamp': row['timestamp'],
      };
    }).toList();

    try {
      final response = await _apiClient.dio.post(
        ApiEndpoints.syncPush,
        data: {
          'device_id': deviceId,
          'actions': actions,
        },
      );

      if (response.statusCode == 200) {
        final results =
            (response.data['results'] as List<dynamic>?) ?? [];

        for (final result in results) {
          final clientId = result['client_id'] as String;
          final status = result['status'] as String;

          if (status == 'processed') {
            await _db.markActionSynced(clientId);
          } else {
            await _db.markActionFailed(
              clientId,
              result['message'] as String? ?? 'Unknown error',
            );
          }
        }
      }
    } catch (_) {
      // Will retry on next cycle
    }
  }

  Future<void> _pushBufferedGpsLogs() async {
    final logs = await _db.getUnsyncedGpsLogs(limit: 100);
    if (logs.isEmpty) return;

    // Convert GPS buffer rows to sync actions
    final actions = logs.map((row) {
      return {
        'client_id': _uuid.v4(),
        'entity_type': 'gps_log',
        'action': 'create',
        'payload': {
          'latitude': row['latitude'],
          'longitude': row['longitude'],
          'altitude': row['altitude'],
          'speed_kmh': row['speed_kmh'],
          'accuracy_meters': row['accuracy_meters'],
          'route_instance_id': row['route_instance_id'],
          'recorded_at': row['recorded_at'],
        },
        'timestamp': row['recorded_at'],
      };
    }).toList();

    try {
      final response = await _apiClient.dio.post(
        ApiEndpoints.syncPush,
        data: {
          'device_id': deviceId,
          'actions': actions,
        },
      );

      if (response.statusCode == 200) {
        final ids = logs.map((r) => r['id'] as int).toList();
        await _db.markGpsLogsSynced(ids);
      }
    } catch (_) {
      // Will retry next cycle
    }
  }

  Future<void> _pullChanges() async {
    final lastPulled = await _db.getSyncMeta('last_pulled_at');

    try {
      final response = await _apiClient.dio.get(
        ApiEndpoints.syncPull,
        queryParameters: {
          'device_id': deviceId,
          if (lastPulled != null) 'since': lastPulled,
        },
      );

      if (response.statusCode == 200) {
        final data = response.data as Map<String, dynamic>;
        final changes = data['changes'] as Map<String, dynamic>? ?? {};

        // Cache each entity type
        for (final entry in changes.entries) {
          final entityName = entry.key;
          final entityData = entry.value as Map<String, dynamic>;
          final records =
              (entityData['data'] as List<dynamic>?) ?? [];

          if (records.isEmpty) continue;

          final tableName = _entityToTable(entityName);
          if (tableName == null) continue;

          final cacheRows = records.map((r) {
            final map = r as Map<String, dynamic>;
            return {
              'id': map['id'],
              'tenant_id': map['tenant_id'],
              if (tableName == 'cached_notifications')
                'user_id': map['user_id'],
              'data': jsonEncode(map),
              'updated_at': map['updated_at'] ?? DateTime.now().toIso8601String(),
            };
          }).toList();

          await _db.cacheRecords(tableName, cacheRows);
        }

        // Update last pulled timestamp
        final syncedAt = data['synced_at'] as String?;
        if (syncedAt != null) {
          await _db.setSyncMeta('last_pulled_at', syncedAt);
        }
      }
    } catch (_) {
      // Will retry next cycle
    }
  }

  String? _entityToTable(String entity) {
    return switch (entity) {
      'stores' => 'cached_stores',
      'products' => 'cached_products',
      'route_plans' => 'cached_route_plans',
      'notifications' => 'cached_notifications',
      _ => null,
    };
  }

  Future<void> _emitStatus() async {
    final pendingCount = await _db.getPendingCount();
    _statusController.add(SyncStatus(
      isSyncing: _isSyncing,
      isOnline: _isOnline,
      pendingActions: pendingCount,
    ));
  }

  void dispose() {
    stop();
    _statusController.close();
  }
}

/// Represents the current sync status.
class SyncStatus {
  const SyncStatus({
    required this.isSyncing,
    required this.isOnline,
    required this.pendingActions,
  });

  final bool isSyncing;
  final bool isOnline;
  final int pendingActions;

  bool get hasPending => pendingActions > 0;
}
