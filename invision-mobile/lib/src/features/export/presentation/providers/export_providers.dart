import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/network/api_client.dart';
import '../../data/models/export_models.dart';
import '../../data/repositories/export_repository.dart';

final exportRepositoryProvider = Provider<ExportRepository>((ref) {
  return ExportRepository(ref.watch(apiClientProvider));
});

final reportTemplatesProvider = FutureProvider<List<ReportTemplateModel>>((ref) {
  return ref.read(exportRepositoryProvider).getReportTemplates();
});

final presentationTemplatesProvider = FutureProvider<List<PresentationTemplateModel>>((ref) {
  return ref.read(exportRepositoryProvider).getPresentationTemplates();
});

final savedExportsProvider = FutureProvider<List<SavedExportModel>>((ref) {
  return ref.read(exportRepositoryProvider).getSavedExports();
});

final marketReviewProvider = FutureProvider.family<PresentationDataModel, String>((ref, period) {
  return ref.read(exportRepositoryProvider).generatePresentation(
        type: 'market_review',
        period: period,
      );
});
