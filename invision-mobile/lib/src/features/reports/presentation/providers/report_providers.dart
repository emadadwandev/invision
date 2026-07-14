import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/network/api_client.dart';
import '../../data/models/report_models.dart';
import '../../data/repositories/report_repository.dart';

final reportRepositoryProvider = Provider<ReportRepository>((ref) {
  return ReportRepository(apiClient: ref.watch(apiClientProvider));
});

/// Provider for a fixed report by slug + date range.
final fixedReportProvider =
    FutureProvider.autoDispose.family<ReportData, FixedReportFilter>((ref, filter) {
  final repo = ref.read(reportRepositoryProvider);
  return repo.getFixedReport(
    filter.slug,
    dateFrom: filter.dateFrom,
    dateTo: filter.dateTo,
  );
});

/// Provider for report entities (dynamic builder metadata).
final reportEntitiesProvider =
    FutureProvider.autoDispose<List<ReportEntity>>((ref) {
  final repo = ref.read(reportRepositoryProvider);
  return repo.getEntities();
});

/// Provider for dynamic report results.
final dynamicReportProvider =
    FutureProvider.autoDispose.family<ReportData, DynamicReportFilter>((ref, filter) {
  final repo = ref.read(reportRepositoryProvider);
  return repo.buildReport(
    entity: filter.entity,
    groupBy: filter.groupBy,
    orderBy: filter.orderBy,
    orderDir: filter.orderDir,
    limit: filter.limit,
    filters: filter.filters,
  );
});

// ─── Filter classes ───────────────────────────────────────────────

class FixedReportFilter {
  final String slug;
  final String? dateFrom;
  final String? dateTo;

  const FixedReportFilter({required this.slug, this.dateFrom, this.dateTo});

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is FixedReportFilter &&
          slug == other.slug &&
          dateFrom == other.dateFrom &&
          dateTo == other.dateTo;

  @override
  int get hashCode => Object.hash(slug, dateFrom, dateTo);
}

class DynamicReportFilter {
  final String entity;
  final String? groupBy;
  final String? orderBy;
  final String orderDir;
  final int limit;
  final Map<String, dynamic>? filters;

  const DynamicReportFilter({
    required this.entity,
    this.groupBy,
    this.orderBy,
    this.orderDir = 'desc',
    this.limit = 100,
    this.filters,
  });

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is DynamicReportFilter &&
          entity == other.entity &&
          groupBy == other.groupBy &&
          orderBy == other.orderBy &&
          orderDir == other.orderDir &&
          limit == other.limit &&
          _mapEquals(filters, other.filters);

  @override
  int get hashCode => Object.hash(entity, groupBy, orderBy, orderDir, limit, filters?.toString());

  static bool _mapEquals(Map<String, dynamic>? a, Map<String, dynamic>? b) {
    if (a == null && b == null) return true;
    if (a == null || b == null) return false;
    if (a.length != b.length) return false;
    for (final key in a.keys) {
      if (a[key] != b[key]) return false;
    }
    return true;
  }
}
