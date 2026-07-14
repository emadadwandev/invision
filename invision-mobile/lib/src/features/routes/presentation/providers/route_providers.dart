import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/network/api_client.dart';
import '../../data/models/route_instance_model.dart';
import '../../data/models/route_plan_model.dart';
import '../../data/repositories/route_repository.dart';

final routeRepositoryProvider = Provider(
  (ref) => RouteRepository(apiClient: ref.watch(apiClientProvider)),
);

final routePlansProvider =
    FutureProvider.autoDispose.family<List<RoutePlan>, RouteFilter>(
  (ref, filter) {
    final repo = ref.watch(routeRepositoryProvider);
    return repo.getRoutePlans(
      search: filter.search,
      status: filter.status,
    );
  },
);

final routePlanDetailProvider =
    FutureProvider.autoDispose.family<RoutePlan, int>(
  (ref, id) {
    final repo = ref.watch(routeRepositoryProvider);
    return repo.getRoutePlan(id);
  },
);

final myRouteTodayProvider =
    FutureProvider.autoDispose<RouteInstance?>(
  (ref) {
    final repo = ref.watch(routeRepositoryProvider);
    return repo.getMyRouteToday();
  },
);

final myRoutesProvider =
    FutureProvider.autoDispose<List<RouteInstance>>(
  (ref) {
    final repo = ref.watch(routeRepositoryProvider);
    return repo.getMyRoutes();
  },
);

final routeInstanceDetailProvider =
    FutureProvider.autoDispose.family<RouteInstance, int>(
  (ref, id) {
    final repo = ref.watch(routeRepositoryProvider);
    return repo.getRouteInstance(id);
  },
);

class RouteFilter {
  const RouteFilter({this.search, this.status});

  final String? search;
  final String? status;

  RouteFilter copyWith({String? search, String? status}) {
    return RouteFilter(
      search: search ?? this.search,
      status: status ?? this.status,
    );
  }

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is RouteFilter &&
          search == other.search &&
          status == other.status;

  @override
  int get hashCode => Object.hash(search, status);
}
