import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/network/api_client.dart';
import '../../data/models/command_center_stats.dart';
import '../../data/models/field_force_position.dart';
import '../../data/models/store_map_item.dart';
import '../../data/models/user_activity.dart';
import '../../data/repositories/command_center_repository.dart';

final commandCenterRepositoryProvider = Provider(
  (ref) => CommandCenterRepository(apiClient: ref.watch(apiClientProvider)),
);

// Dashboard stats
final commandCenterStatsProvider =
    FutureProvider.autoDispose<CommandCenterStats>(
  (ref) {
    final repo = ref.watch(commandCenterRepositoryProvider);
    return repo.getStats();
  },
);

// Field force positions (auto-refreshable)
final fieldForcePositionsProvider =
    FutureProvider.autoDispose<List<FieldForcePosition>>(
  (ref) {
    final repo = ref.watch(commandCenterRepositoryProvider);
    return repo.getFieldForcePositions();
  },
);

// Store map data
final storeMapDataProvider =
    FutureProvider.autoDispose<List<StoreMapItem>>(
  (ref) {
    final repo = ref.watch(commandCenterRepositoryProvider);
    return repo.getStoreMapData();
  },
);

// Store inquiry (by store ID)
final storeInquiryProvider =
    FutureProvider.autoDispose.family<Map<String, dynamic>, int>(
  (ref, storeId) {
    final repo = ref.watch(commandCenterRepositoryProvider);
    return repo.getStoreInquiry(storeId);
  },
);

// User activity & GPS trail (by user ID)
final userActivityProvider =
    FutureProvider.autoDispose.family<UserActivity, int>(
  (ref, userId) {
    final repo = ref.watch(commandCenterRepositoryProvider);
    return repo.getUserActivity(userId);
  },
);
