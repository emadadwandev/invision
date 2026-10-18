import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/network/api_client.dart';
import '../../../competitors/data/models/competitor_models.dart';
import '../../../competitors/data/repositories/competitor_repository.dart';
import '../../../products/data/models/product_model.dart';
import '../../../products/data/repositories/product_repository.dart';
import '../../data/models/visit_capture_model.dart';
import '../../data/repositories/visit_capture_repository.dart';

final visitCaptureRepositoryProvider = Provider(
  (ref) => VisitCaptureRepository(apiClient: ref.watch(apiClientProvider)),
);

final visitCaptureProvider = FutureProvider.autoDispose
    .family<VisitCapture, int>((ref, visitId) {
      return ref.watch(visitCaptureRepositoryProvider).getCapture(visitId);
    });

final visitProductsProvider = FutureProvider.autoDispose<List<Product>>((ref) {
  final repository = ProductRepository(apiClient: ref.watch(apiClientProvider));
  return repository.getProducts(perPage: 1000);
});

final visitCompetitorsProvider = FutureProvider.autoDispose<List<Competitor>>((
  ref,
) {
  final repository = CompetitorRepository(
    apiClient: ref.watch(apiClientProvider),
  );
  return repository.getCompetitors(perPage: 1000);
});
