import '../../../../core/constants/app_constants.dart';
import '../../../../core/network/api_client.dart';
import '../models/competitor_models.dart';

class CompetitorRepository {
  CompetitorRepository({ApiClient? apiClient})
      : _client = apiClient ?? ApiClient();
  final ApiClient _client;

  // ─── Competitors ─────────────────────────────────────────

  Future<List<Competitor>> getCompetitors({String? search}) async {
    final queryParams = <String, dynamic>{};
    if (search != null && search.isNotEmpty) queryParams['search'] = search;

    final response = await _client.dio
        .get(ApiEndpoints.competitors, queryParameters: queryParams);
    final data = response.data['data'] as List;
    return data
        .map((json) => Competitor.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<Competitor> getCompetitor(int id) async {
    final response =
        await _client.dio.get(ApiEndpoints.competitor(id));
    return Competitor.fromJson(response.data as Map<String, dynamic>);
  }

  // ─── Competitor Products ─────────────────────────────────

  Future<List<CompetitorProduct>> getProducts({int? competitorId}) async {
    final queryParams = <String, dynamic>{};
    if (competitorId != null) {
      queryParams['competitor_id'] = competitorId;
    }

    final response = await _client.dio
        .get(ApiEndpoints.competitorProducts, queryParameters: queryParams);
    final data = response.data['data'] as List;
    return data
        .map((json) =>
            CompetitorProduct.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  // ─── Observations ────────────────────────────────────────

  Future<List<CompetitorObservation>> getObservations({
    int? storeId,
    int? competitorId,
    String? observationType,
    int? storeVisitId,
  }) async {
    final queryParams = <String, dynamic>{};
    if (storeId != null) queryParams['store_id'] = storeId;
    if (competitorId != null) queryParams['competitor_id'] = competitorId;
    if (observationType != null) {
      queryParams['observation_type'] = observationType;
    }
    if (storeVisitId != null) {
      queryParams['store_visit_id'] = storeVisitId;
    }

    final response = await _client.dio.get(
        ApiEndpoints.competitorObservations,
        queryParameters: queryParams);
    final data = response.data['data'] as List;
    return data
        .map((json) =>
            CompetitorObservation.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<CompetitorObservation> createObservation({
    required int storeId,
    required String observationType,
    int? storeVisitId,
    int? competitorId,
    int? competitorProductId,
    int? quantity,
    double? price,
    String? notes,
    double? latitude,
    double? longitude,
  }) async {
    final data = <String, dynamic>{
      'store_id': storeId,
      'observation_type': observationType,
    };
    if (storeVisitId != null) data['store_visit_id'] = storeVisitId;
    if (competitorId != null) data['competitor_id'] = competitorId;
    if (competitorProductId != null) {
      data['competitor_product_id'] = competitorProductId;
    }
    if (quantity != null) data['quantity'] = quantity;
    if (price != null) data['price'] = price;
    if (notes != null) data['notes'] = notes;
    if (latitude != null) data['latitude'] = latitude;
    if (longitude != null) data['longitude'] = longitude;

    final response =
        await _client.dio.post(ApiEndpoints.competitorObservations, data: data);
    return CompetitorObservation.fromJson(
        response.data as Map<String, dynamic>);
  }

  // ─── Visit Observations ──────────────────────────────────

  Future<List<CompetitorObservation>> getVisitObservations(
      int storeVisitId) async {
    final response = await _client.dio
        .get(ApiEndpoints.visitCompetitorObservations(storeVisitId));
    final data = response.data['data'] as List;
    return data
        .map((json) =>
            CompetitorObservation.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  // ─── Analysis ────────────────────────────────────────────

  Future<List<CompetitorAnalysisItem>> getAnalysis({
    int? storeId,
    String? from,
    String? to,
  }) async {
    final queryParams = <String, dynamic>{};
    if (storeId != null) queryParams['store_id'] = storeId;
    if (from != null) queryParams['from'] = from;
    if (to != null) queryParams['to'] = to;

    final response = await _client.dio
        .get(ApiEndpoints.competitorAnalysis, queryParameters: queryParams);
    final data = response.data['data'] as List;
    return data
        .map((json) =>
            CompetitorAnalysisItem.fromJson(json as Map<String, dynamic>))
        .toList();
  }
}
