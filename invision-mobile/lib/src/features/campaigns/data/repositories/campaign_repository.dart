import '../../../../core/constants/app_constants.dart';
import '../../../../core/network/api_client.dart';
import '../models/campaign_model.dart';
import '../models/campaign_task_model.dart';

class CampaignRepository {
  CampaignRepository({ApiClient? apiClient})
      : _client = apiClient ?? ApiClient();

  final ApiClient _client;

  // Campaigns
  Future<List<Campaign>> getCampaigns({
    String? search,
    String? status,
    String? type,
  }) async {
    final queryParams = <String, dynamic>{};
    if (search != null && search.isNotEmpty) queryParams['search'] = search;
    if (status != null && status.isNotEmpty) queryParams['status'] = status;
    if (type != null && type.isNotEmpty) queryParams['type'] = type;

    final response = await _client.dio.get(
      ApiEndpoints.campaigns,
      queryParameters: queryParams,
    );
    final data = response.data['data'] as List;
    return data
        .map((json) => Campaign.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<Campaign> getCampaign(int id) async {
    final response = await _client.dio.get(ApiEndpoints.campaign(id));
    return Campaign.fromJson(response.data['data'] as Map<String, dynamic>);
  }

  // Campaign Tasks
  Future<List<CampaignTask>> getCampaignTasks(int campaignId) async {
    final response =
        await _client.dio.get(ApiEndpoints.campaignTasks(campaignId));
    final data = response.data['data'] as List;
    return data
        .map((json) => CampaignTask.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<CampaignTask> getTask(int taskId) async {
    final response =
        await _client.dio.get(ApiEndpoints.campaignTask(taskId));
    return CampaignTask.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }

  Future<CampaignTask> completeTask(int taskId) async {
    final response =
        await _client.dio.post(ApiEndpoints.completeTask(taskId));
    return CampaignTask.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }

  // My Tasks (Mobile)
  Future<List<CampaignTask>> getMyTasks({String? status}) async {
    final queryParams = <String, dynamic>{};
    if (status != null && status.isNotEmpty) queryParams['status'] = status;

    final response = await _client.dio.get(
      ApiEndpoints.myTasks,
      queryParameters: queryParams,
    );
    final data = response.data['data'] as List;
    return data
        .map((json) => CampaignTask.fromJson(json as Map<String, dynamic>))
        .toList();
  }
}
