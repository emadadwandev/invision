import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/network/api_client.dart';
import '../../data/models/dashboard_models.dart';
import '../../data/models/inquiry_models.dart';
import '../../data/repositories/dashboard_repository.dart';

final dashboardRepositoryProvider = Provider(
  (ref) => DashboardRepository(apiClient: ref.watch(apiClientProvider)),
);

final overviewKpiProvider = FutureProvider.autoDispose<OverviewKpi>(
  (ref) => ref.watch(dashboardRepositoryProvider).getOverview(),
);

final salesKpiProvider =
    FutureProvider.autoDispose.family<SalesKpi, String>(
  (ref, period) =>
      ref.watch(dashboardRepositoryProvider).getSalesKpi(period: period),
);

final routeKpiProvider =
    FutureProvider.autoDispose.family<RouteKpi, String>(
  (ref, period) =>
      ref.watch(dashboardRepositoryProvider).getRouteKpi(period: period),
);

final campaignKpiProvider = FutureProvider.autoDispose<CampaignKpi>(
  (ref) => ref.watch(dashboardRepositoryProvider).getCampaignKpi(),
);

// Inquiry providers

final storeInquiryProvider = FutureProvider.autoDispose
    .family<List<StoreInquiryItem>, StoreInquiryFilter>(
  (ref, filter) => ref.watch(dashboardRepositoryProvider).getStoreInquiry(
        search: filter.search,
        category: filter.category,
        rank: filter.rank,
      ),
);

final salesInquiryProvider = FutureProvider.autoDispose
    .family<List<SalesInquiryItem>, SalesInquiryFilter>(
  (ref, filter) => ref.watch(dashboardRepositoryProvider).getSalesInquiry(
        status: filter.status,
        search: filter.search,
        dateFrom: filter.dateFrom,
        dateTo: filter.dateTo,
      ),
);

final routeInquiryProvider = FutureProvider.autoDispose
    .family<List<RouteInquiryItem>, RouteInquiryFilter>(
  (ref, filter) => ref.watch(dashboardRepositoryProvider).getRouteInquiry(
        status: filter.status,
        dateFrom: filter.dateFrom,
        dateTo: filter.dateTo,
      ),
);

// Filter classes

class StoreInquiryFilter {
  const StoreInquiryFilter({this.search, this.category, this.rank});

  final String? search;
  final String? category;
  final String? rank;

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is StoreInquiryFilter &&
          search == other.search &&
          category == other.category &&
          rank == other.rank;

  @override
  int get hashCode => Object.hash(search, category, rank);
}

class SalesInquiryFilter {
  const SalesInquiryFilter({
    this.status,
    this.search,
    this.dateFrom,
    this.dateTo,
  });

  final String? status;
  final String? search;
  final String? dateFrom;
  final String? dateTo;

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is SalesInquiryFilter &&
          status == other.status &&
          search == other.search &&
          dateFrom == other.dateFrom &&
          dateTo == other.dateTo;

  @override
  int get hashCode => Object.hash(status, search, dateFrom, dateTo);
}

class RouteInquiryFilter {
  const RouteInquiryFilter({this.status, this.dateFrom, this.dateTo});

  final String? status;
  final String? dateFrom;
  final String? dateTo;

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is RouteInquiryFilter &&
          status == other.status &&
          dateFrom == other.dateFrom &&
          dateTo == other.dateTo;

  @override
  int get hashCode => Object.hash(status, dateFrom, dateTo);
}
