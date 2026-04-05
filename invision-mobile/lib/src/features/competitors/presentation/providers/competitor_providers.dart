import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/network/api_client.dart';
import '../../data/models/competitor_models.dart';
import '../../data/repositories/competitor_repository.dart';

final competitorRepositoryProvider = Provider(
  (ref) => CompetitorRepository(apiClient: ref.watch(apiClientProvider)),
);

final competitorsProvider =
    FutureProvider.autoDispose.family<List<Competitor>, String?>(
  (ref, search) {
    final repo = ref.watch(competitorRepositoryProvider);
    return repo.getCompetitors(search: search);
  },
);

final competitorDetailProvider =
    FutureProvider.autoDispose.family<Competitor, int>(
  (ref, id) {
    final repo = ref.watch(competitorRepositoryProvider);
    return repo.getCompetitor(id);
  },
);

final competitorProductsProvider =
    FutureProvider.autoDispose.family<List<CompetitorProduct>, int?>(
  (ref, competitorId) {
    final repo = ref.watch(competitorRepositoryProvider);
    return repo.getProducts(competitorId: competitorId);
  },
);

final competitorObservationsProvider = FutureProvider.autoDispose
    .family<List<CompetitorObservation>, ObservationFilter>(
  (ref, filter) {
    final repo = ref.watch(competitorRepositoryProvider);
    return repo.getObservations(
      storeId: filter.storeId,
      competitorId: filter.competitorId,
      observationType: filter.observationType,
      storeVisitId: filter.storeVisitId,
    );
  },
);

final visitObservationsProvider = FutureProvider.autoDispose
    .family<List<CompetitorObservation>, int>(
  (ref, storeVisitId) {
    final repo = ref.watch(competitorRepositoryProvider);
    return repo.getVisitObservations(storeVisitId);
  },
);

final competitorAnalysisProvider = FutureProvider.autoDispose
    .family<List<CompetitorAnalysisItem>, AnalysisFilter>(
  (ref, filter) {
    final repo = ref.watch(competitorRepositoryProvider);
    return repo.getAnalysis(
      storeId: filter.storeId,
      from: filter.from,
      to: filter.to,
    );
  },
);

class ObservationFilter {
  const ObservationFilter({
    this.storeId,
    this.competitorId,
    this.observationType,
    this.storeVisitId,
  });

  final int? storeId;
  final int? competitorId;
  final String? observationType;
  final int? storeVisitId;

  ObservationFilter copyWith({
    int? storeId,
    int? competitorId,
    String? observationType,
    int? storeVisitId,
  }) {
    return ObservationFilter(
      storeId: storeId ?? this.storeId,
      competitorId: competitorId ?? this.competitorId,
      observationType: observationType ?? this.observationType,
      storeVisitId: storeVisitId ?? this.storeVisitId,
    );
  }

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is ObservationFilter &&
          storeId == other.storeId &&
          competitorId == other.competitorId &&
          observationType == other.observationType &&
          storeVisitId == other.storeVisitId;

  @override
  int get hashCode =>
      Object.hash(storeId, competitorId, observationType, storeVisitId);
}

class AnalysisFilter {
  const AnalysisFilter({this.storeId, this.from, this.to});

  final int? storeId;
  final String? from;
  final String? to;

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is AnalysisFilter &&
          storeId == other.storeId &&
          from == other.from &&
          to == other.to;

  @override
  int get hashCode => Object.hash(storeId, from, to);
}
