import 'package:path/path.dart';
import 'package:sqflite/sqflite.dart';

/// Local SQLite database for offline data caching and sync queue.
class OfflineDatabase {
  OfflineDatabase._();
  static final OfflineDatabase instance = OfflineDatabase._();

  Database? _database;

  Future<Database> get database async {
    _database ??= await _initDb();
    return _database!;
  }

  Future<Database> _initDb() async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, 'invision_offline.db');

    return openDatabase(
      path,
      version: 1,
      onCreate: _onCreate,
    );
  }

  Future<void> _onCreate(Database db, int version) async {
    // Offline action queue — actions performed while offline
    await db.execute('''
      CREATE TABLE sync_queue (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        client_id TEXT UNIQUE NOT NULL,
        entity_type TEXT NOT NULL,
        action TEXT NOT NULL,
        payload TEXT NOT NULL,
        timestamp TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'pending',
        retry_count INTEGER NOT NULL DEFAULT 0,
        error_message TEXT,
        created_at TEXT NOT NULL
      )
    ''');

    // Cached stores
    await db.execute('''
      CREATE TABLE cached_stores (
        id INTEGER PRIMARY KEY,
        tenant_id INTEGER NOT NULL,
        data TEXT NOT NULL,
        updated_at TEXT NOT NULL
      )
    ''');

    // Cached products
    await db.execute('''
      CREATE TABLE cached_products (
        id INTEGER PRIMARY KEY,
        tenant_id INTEGER NOT NULL,
        data TEXT NOT NULL,
        updated_at TEXT NOT NULL
      )
    ''');

    // Cached route plans
    await db.execute('''
      CREATE TABLE cached_route_plans (
        id INTEGER PRIMARY KEY,
        tenant_id INTEGER NOT NULL,
        data TEXT NOT NULL,
        updated_at TEXT NOT NULL
      )
    ''');

    // Cached notifications
    await db.execute('''
      CREATE TABLE cached_notifications (
        id INTEGER PRIMARY KEY,
        tenant_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        data TEXT NOT NULL,
        updated_at TEXT NOT NULL
      )
    ''');

    // GPS log buffer — for batching GPS logs when offline
    await db.execute('''
      CREATE TABLE gps_log_buffer (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        latitude REAL NOT NULL,
        longitude REAL NOT NULL,
        altitude REAL,
        speed_kmh REAL,
        accuracy_meters REAL,
        route_instance_id INTEGER,
        recorded_at TEXT NOT NULL,
        synced INTEGER NOT NULL DEFAULT 0
      )
    ''');

    // Sync metadata
    await db.execute('''
      CREATE TABLE sync_meta (
        key TEXT PRIMARY KEY,
        value TEXT NOT NULL
      )
    ''');
  }

  // ---- Sync Queue Operations ----

  Future<int> enqueueAction({
    required String clientId,
    required String entityType,
    required String action,
    required String payload,
    required String timestamp,
  }) async {
    final db = await database;
    return db.insert('sync_queue', {
      'client_id': clientId,
      'entity_type': entityType,
      'action': action,
      'payload': payload,
      'timestamp': timestamp,
      'status': 'pending',
      'retry_count': 0,
      'created_at': DateTime.now().toIso8601String(),
    });
  }

  Future<List<Map<String, dynamic>>> getPendingActions({int limit = 50}) async {
    final db = await database;
    return db.query(
      'sync_queue',
      where: 'status = ?',
      whereArgs: ['pending'],
      orderBy: 'timestamp ASC',
      limit: limit,
    );
  }

  Future<int> markActionSynced(String clientId) async {
    final db = await database;
    return db.update(
      'sync_queue',
      {'status': 'synced'},
      where: 'client_id = ?',
      whereArgs: [clientId],
    );
  }

  Future<int> markActionFailed(String clientId, String error) async {
    final db = await database;
    return db.rawUpdate(
      'UPDATE sync_queue SET status = ?, error_message = ?, retry_count = retry_count + 1 WHERE client_id = ?',
      ['failed', error, clientId],
    );
  }

  Future<int> resetFailedActions() async {
    final db = await database;
    return db.update(
      'sync_queue',
      {'status': 'pending'},
      where: 'status = ? AND retry_count < ?',
      whereArgs: ['failed', 5],
    );
  }

  Future<int> getPendingCount() async {
    final db = await database;
    final result = await db.rawQuery(
      'SELECT COUNT(*) as count FROM sync_queue WHERE status = ?',
      ['pending'],
    );
    return (result.first['count'] as int?) ?? 0;
  }

  Future<void> clearSyncedActions() async {
    final db = await database;
    await db.delete(
      'sync_queue',
      where: 'status = ?',
      whereArgs: ['synced'],
    );
  }

  // ---- Cache Operations ----

  Future<void> cacheRecords(
    String tableName,
    List<Map<String, dynamic>> records,
  ) async {
    final db = await database;
    final batch = db.batch();

    for (final record in records) {
      batch.insert(
        tableName,
        record,
        conflictAlgorithm: ConflictAlgorithm.replace,
      );
    }

    await batch.commit(noResult: true);
  }

  Future<List<Map<String, dynamic>>> getCachedRecords(String tableName) async {
    final db = await database;
    return db.query(tableName, orderBy: 'updated_at DESC');
  }

  Future<Map<String, dynamic>?> getCachedRecord(
    String tableName,
    int id,
  ) async {
    final db = await database;
    final results = await db.query(
      tableName,
      where: 'id = ?',
      whereArgs: [id],
    );
    return results.isEmpty ? null : results.first;
  }

  // ---- GPS Buffer ----

  Future<int> bufferGpsLog({
    required double latitude,
    required double longitude,
    double? altitude,
    double? speedKmh,
    double? accuracyMeters,
    int? routeInstanceId,
    required String recordedAt,
  }) async {
    final db = await database;
    return db.insert('gps_log_buffer', {
      'latitude': latitude,
      'longitude': longitude,
      'altitude': altitude,
      'speed_kmh': speedKmh,
      'accuracy_meters': accuracyMeters,
      'route_instance_id': routeInstanceId,
      'recorded_at': recordedAt,
      'synced': 0,
    });
  }

  Future<List<Map<String, dynamic>>> getUnsyncedGpsLogs({
    int limit = 100,
  }) async {
    final db = await database;
    return db.query(
      'gps_log_buffer',
      where: 'synced = ?',
      whereArgs: [0],
      orderBy: 'recorded_at ASC',
      limit: limit,
    );
  }

  Future<void> markGpsLogsSynced(List<int> ids) async {
    if (ids.isEmpty) return;
    final db = await database;
    final placeholders = List.filled(ids.length, '?').join(',');
    await db.rawUpdate(
      'UPDATE gps_log_buffer SET synced = 1 WHERE id IN ($placeholders)',
      ids,
    );
  }

  Future<void> clearSyncedGpsLogs() async {
    final db = await database;
    await db.delete(
      'gps_log_buffer',
      where: 'synced = ?',
      whereArgs: [1],
    );
  }

  // ---- Sync Metadata ----

  Future<void> setSyncMeta(String key, String value) async {
    final db = await database;
    await db.insert(
      'sync_meta',
      {'key': key, 'value': value},
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<String?> getSyncMeta(String key) async {
    final db = await database;
    final results = await db.query(
      'sync_meta',
      where: 'key = ?',
      whereArgs: [key],
    );
    return results.isEmpty ? null : results.first['value'] as String?;
  }

  // ---- Cleanup ----

  Future<void> clearAll() async {
    final db = await database;
    await db.delete('sync_queue');
    await db.delete('cached_stores');
    await db.delete('cached_products');
    await db.delete('cached_route_plans');
    await db.delete('cached_notifications');
    await db.delete('gps_log_buffer');
    await db.delete('sync_meta');
  }
}
