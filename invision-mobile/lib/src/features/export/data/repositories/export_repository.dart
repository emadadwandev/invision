import 'package:dio/dio.dart';

import '../../../../core/constants/app_constants.dart';
import '../../../../core/network/api_client.dart';
import '../models/export_models.dart';

class ExportRepository {
  ExportRepository(this._apiClient);

  final ApiClient _apiClient;

  // ─── Report Templates ───────────────────────────────────────────

  Future<List<ReportTemplateModel>> getReportTemplates() async {
    final response = await _apiClient.dio.get(ApiEndpoints.reportTemplates);
    final list = response.data['data'] as List;
    return list.map((e) => ReportTemplateModel.fromJson(e)).toList();
  }

  Future<ReportTemplateModel> createReportTemplate(Map<String, dynamic> data) async {
    final response = await _apiClient.dio.post(ApiEndpoints.reportTemplates, data: data);
    return ReportTemplateModel.fromJson(response.data['data']);
  }

  Future<ReportTemplateModel> getReportTemplate(int id) async {
    final response = await _apiClient.dio.get(ApiEndpoints.reportTemplate(id));
    return ReportTemplateModel.fromJson(response.data['data']);
  }

  Future<void> updateReportTemplate(int id, Map<String, dynamic> data) async {
    await _apiClient.dio.put(ApiEndpoints.reportTemplate(id), data: data);
  }

  Future<void> deleteReportTemplate(int id) async {
    await _apiClient.dio.delete(ApiEndpoints.reportTemplate(id));
  }

  Future<Map<String, dynamic>> generateFromTemplate(int id, {String? dateFrom, String? dateTo}) async {
    final response = await _apiClient.dio.post(
      ApiEndpoints.reportTemplateGenerate(id),
      data: {
        if (dateFrom != null) 'date_from': dateFrom,
        if (dateTo != null) 'date_to': dateTo,
      },
    );
    return response.data['data'];
  }

  Future<Response> exportTemplateExcel(int id) async {
    return _apiClient.dio.get(
      ApiEndpoints.reportTemplateExportExcel(id),
      options: Options(responseType: ResponseType.bytes),
    );
  }

  Future<Response> exportTemplatePdf(int id) async {
    return _apiClient.dio.get(
      ApiEndpoints.reportTemplateExportPdf(id),
      options: Options(responseType: ResponseType.bytes),
    );
  }

  Future<Map<String, dynamic>> exportTemplateCsv(int id) async {
    final response = await _apiClient.dio.get(ApiEndpoints.reportTemplateExportCsv(id));
    return response.data['data'];
  }

  // ─── Presentation Templates ─────────────────────────────────────

  Future<List<PresentationTemplateModel>> getPresentationTemplates() async {
    final response = await _apiClient.dio.get(ApiEndpoints.presentationTemplates);
    final list = response.data['data'] as List;
    return list.map((e) => PresentationTemplateModel.fromJson(e)).toList();
  }

  Future<PresentationTemplateModel> createPresentationTemplate(Map<String, dynamic> data) async {
    final response = await _apiClient.dio.post(ApiEndpoints.presentationTemplates, data: data);
    return PresentationTemplateModel.fromJson(response.data['data']);
  }

  // ─── Generate Presentations ─────────────────────────────────────

  Future<PresentationDataModel> generatePresentation({
    required String type,
    int? templateId,
    String? period,
  }) async {
    final response = await _apiClient.dio.post(
      ApiEndpoints.presentationGenerate,
      data: {
        'type': type,
        if (templateId != null) 'template_id': templateId,
        if (period != null) 'period': period,
      },
    );
    return PresentationDataModel.fromJson(response.data['data']);
  }

  Future<Map<String, dynamic>> generatePresentationHtml({
    required String type,
    int? templateId,
    String? period,
  }) async {
    final response = await _apiClient.dio.post(
      ApiEndpoints.presentationGenerateHtml,
      data: {
        'type': type,
        if (templateId != null) 'template_id': templateId,
        if (period != null) 'period': period,
      },
    );
    return response.data['data'];
  }

  // ─── Saved Exports ──────────────────────────────────────────────

  Future<List<SavedExportModel>> getSavedExports() async {
    final response = await _apiClient.dio.get(ApiEndpoints.savedExports);
    final list = response.data['data'] as List;
    return list.map((e) => SavedExportModel.fromJson(e)).toList();
  }

  Future<void> deleteSavedExport(int id) async {
    await _apiClient.dio.delete(ApiEndpoints.savedExport(id));
  }
}
