import 'package:dio/dio.dart';

import '../../../../core/constants/app_constants.dart';
import '../../../../core/network/api_client.dart';
import '../models/route_instance_model.dart';
import '../models/route_plan_model.dart';

class RouteRepository {
  RouteRepository({ApiClient? apiClient})
      : _client = apiClient ?? ApiClient();

  final ApiClient _client;

  // Route Plans
  Future<List<RoutePlan>> getRoutePlans({String? search, String? status}) async {
    final queryParams = <String, dynamic>{};
    if (search != null && search.isNotEmpty) queryParams['search'] = search;
    if (status != null && status.isNotEmpty) queryParams['status'] = status;

    final response = await _client.dio.get(
      ApiEndpoints.routePlans,
      queryParameters: queryParams,
    );
    final data = response.data['data'] as List;
    return data
        .map((json) => RoutePlan.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<RoutePlan> getRoutePlan(int id) async {
    final response = await _client.dio.get(ApiEndpoints.routePlan(id));
    return RoutePlan.fromJson(response.data['data'] as Map<String, dynamic>);
  }

  // My Routes (Mobile)
  Future<RouteInstance?> getMyRouteToday() async {
    final response = await _client.dio.get(ApiEndpoints.myRouteToday);
    final data = response.data['data'];
    if (data == null) return null;
    return RouteInstance.fromJson(data as Map<String, dynamic>);
  }

  Future<List<RouteInstance>> getMyRoutes({
    String? dateFrom,
    String? dateTo,
  }) async {
    final queryParams = <String, dynamic>{};
    if (dateFrom != null) queryParams['date_from'] = dateFrom;
    if (dateTo != null) queryParams['date_to'] = dateTo;

    final response = await _client.dio.get(
      ApiEndpoints.myRoutes,
      queryParameters: queryParams,
    );
    final data = response.data['data'] as List;
    return data
        .map((json) => RouteInstance.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  // Route Instances
  Future<RouteInstance> getRouteInstance(int id) async {
    final response = await _client.dio.get(ApiEndpoints.routeInstance(id));
    return RouteInstance.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }

  Future<RouteInstance> startRouteInstance(int id) async {
    final response =
        await _client.dio.post(ApiEndpoints.startRouteInstance(id));
    return RouteInstance.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }

  // Store Visits
  Future<StoreVisit> checkIn(
    int visitId, {
    required double latitude,
    required double longitude,
    String? qrCode,
  }) async {
    try {
      final response = await _client.dio.post(
        ApiEndpoints.visitCheckIn(visitId),
        data: {
          'latitude': latitude,
          'longitude': longitude,
          if (qrCode != null) 'qr_code': qrCode,
        },
      );
      return StoreVisit.fromJson(response.data['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      final message = e.response?.data?['message'] as String?;
      throw Exception(message ?? 'Check-in failed');
    }
  }

  Future<StoreVisit> checkOut(
    int visitId, {
    required double latitude,
    required double longitude,
    String? notes,
  }) async {
    try {
      final response = await _client.dio.post(
        ApiEndpoints.visitCheckOut(visitId),
        data: {
          'latitude': latitude,
          'longitude': longitude,
          if (notes != null) 'notes': notes,
        },
      );
      return StoreVisit.fromJson(response.data['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      final message = e.response?.data?['message'] as String?;
      throw Exception(message ?? 'Check-out failed');
    }
  }

  Future<StoreVisit> skipVisit(int visitId, {required String reason}) async {
    final response = await _client.dio.post(
      ApiEndpoints.visitSkip(visitId),
      data: {'reason': reason},
    );
    return StoreVisit.fromJson(response.data['data'] as Map<String, dynamic>);
  }

  // GPS Tracking
  Future<void> logGps({
    required double latitude,
    required double longitude,
    double? accuracy,
    double? speed,
    double? bearing,
    int? routeInstanceId,
  }) async {
    await _client.dio.post(
      ApiEndpoints.gpsLog,
      data: {
        'latitude': latitude,
        'longitude': longitude,
        if (accuracy != null) 'accuracy_meters': accuracy,
        if (speed != null) 'speed_kmh': speed,
        if (bearing != null) 'bearing': bearing,
        if (routeInstanceId != null) 'route_instance_id': routeInstanceId,
      },
    );
  }

  Future<void> batchLogGps(List<Map<String, dynamic>> logs) async {
    await _client.dio.post(
      ApiEndpoints.gpsLog,
      data: {'logs': logs},
    );
  }
}
