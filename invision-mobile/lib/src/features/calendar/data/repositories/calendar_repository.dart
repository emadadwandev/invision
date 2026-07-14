import '../../../../core/constants/app_constants.dart';
import '../../../../core/network/api_client.dart';
import '../models/calendar_models.dart';

class CalendarRepository {
  CalendarRepository({ApiClient? apiClient})
      : _client = apiClient ?? ApiClient();
  final ApiClient _client;

  Future<List<CalendarEventModel>> getEvents({
    String? from,
    String? to,
    String? type,
  }) async {
    final params = <String, dynamic>{};
    if (from != null) params['from'] = from;
    if (to != null) params['to'] = to;
    if (type != null) params['type'] = type;

    final response = await _client.dio
        .get(ApiEndpoints.calendarEvents, queryParameters: params);
    final data = response.data['data'] as List;
    return data
        .map((j) => CalendarEventModel.fromJson(j as Map<String, dynamic>))
        .toList();
  }

  Future<List<HolidayModel>> getHolidays({int? year}) async {
    final params = <String, dynamic>{};
    if (year != null) params['year'] = year;

    final response = await _client.dio
        .get(ApiEndpoints.calendarHolidays, queryParameters: params);
    final data = response.data['data'] as List;
    return data
        .map((j) => HolidayModel.fromJson(j as Map<String, dynamic>))
        .toList();
  }

  Future<List<SalesAreaModel>> getSalesAreas({String? search}) async {
    final params = <String, dynamic>{};
    if (search != null && search.isNotEmpty) params['search'] = search;

    final response =
        await _client.dio.get(ApiEndpoints.salesAreas, queryParameters: params);
    final data = response.data['data'] as List;
    return data
        .map((j) => SalesAreaModel.fromJson(j as Map<String, dynamic>))
        .toList();
  }

  Future<List<SalesAreaModel>> getSalesAreasHierarchy() async {
    final response = await _client.dio.get(ApiEndpoints.salesAreasHierarchy);
    final data = response.data['data'] as List;
    return data
        .map((j) => SalesAreaModel.fromJson(j as Map<String, dynamic>))
        .toList();
  }

  Future<SalesAreaModel> getSalesArea(int id) async {
    final response = await _client.dio.get(ApiEndpoints.salesArea(id));
    return SalesAreaModel.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }
}
