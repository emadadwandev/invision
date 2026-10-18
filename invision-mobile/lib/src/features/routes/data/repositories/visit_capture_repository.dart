import '../../../../core/constants/app_constants.dart';
import '../../../../core/network/api_client.dart';
import '../models/visit_capture_model.dart';

class VisitCaptureRepository {
  VisitCaptureRepository({ApiClient? apiClient})
    : _client = apiClient ?? ApiClient();

  final ApiClient _client;

  Future<VisitCapture> getCapture(int visitId) async {
    final response = await _client.dio.get(ApiEndpoints.visitCapture(visitId));
    return VisitCapture.fromJson(response.data['data'] as Map<String, dynamic>);
  }

  Future<VisitCapture> saveCapture(
    int visitId, {
    required String remarks,
    required List<Map<String, dynamic>> items,
  }) async {
    final response = await _client.dio.put(
      ApiEndpoints.visitCapture(visitId),
      data: {'remarks': remarks, 'items': items},
    );
    return VisitCapture.fromJson(response.data['data'] as Map<String, dynamic>);
  }
}
