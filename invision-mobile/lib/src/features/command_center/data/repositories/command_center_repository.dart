import '../../../../core/constants/app_constants.dart';
import '../../../../core/network/api_client.dart';
import '../models/command_center_stats.dart';
import '../models/field_force_position.dart';
import '../models/store_map_item.dart';
import '../models/user_activity.dart';

class CommandCenterRepository {
  CommandCenterRepository({ApiClient? apiClient})
      : _client = apiClient ?? ApiClient();

  final ApiClient _client;

  Future<CommandCenterStats> getStats() async {
    final response = await _client.dio.get(ApiEndpoints.commandCenterStats);
    return CommandCenterStats.fromJson(
      response.data['data'] as Map<String, dynamic>,
    );
  }

  Future<List<FieldForcePosition>> getFieldForcePositions() async {
    final response =
        await _client.dio.get(ApiEndpoints.commandCenterFieldForce);
    final data = response.data['data'] as List;
    return data
        .map(
          (json) =>
              FieldForcePosition.fromJson(json as Map<String, dynamic>),
        )
        .toList();
  }

  Future<List<StoreMapItem>> getStoreMapData() async {
    final response = await _client.dio.get(ApiEndpoints.commandCenterStores);
    final data = response.data['data'] as List;
    return data
        .map(
          (json) => StoreMapItem.fromJson(json as Map<String, dynamic>),
        )
        .toList();
  }

  Future<Map<String, dynamic>> getStoreInquiry(int storeId) async {
    final response = await _client.dio.get(
      ApiEndpoints.commandCenterStoreInquiry(storeId),
    );
    return response.data['data'] as Map<String, dynamic>;
  }

  Future<UserActivity> getUserActivity(int userId) async {
    final response = await _client.dio.get(
      ApiEndpoints.commandCenterUserActivity(userId),
    );
    return UserActivity.fromJson(
      response.data['data'] as Map<String, dynamic>,
    );
  }
}
