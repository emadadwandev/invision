import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/network/api_client.dart';
import '../../data/models/calendar_models.dart';
import '../../data/repositories/calendar_repository.dart';

final calendarRepositoryProvider = Provider(
  (ref) => CalendarRepository(apiClient: ref.watch(apiClientProvider)),
);

final calendarEventsProvider =
    FutureProvider.autoDispose<List<CalendarEventModel>>((ref) {
  final repo = ref.watch(calendarRepositoryProvider);
  return repo.getEvents();
});

final holidaysProvider =
    FutureProvider.autoDispose.family<List<HolidayModel>, int?>((ref, year) {
  final repo = ref.watch(calendarRepositoryProvider);
  return repo.getHolidays(year: year);
});

final salesAreasProvider =
    FutureProvider.autoDispose<List<SalesAreaModel>>((ref) {
  final repo = ref.watch(calendarRepositoryProvider);
  return repo.getSalesAreas();
});

final salesAreasHierarchyProvider =
    FutureProvider.autoDispose<List<SalesAreaModel>>((ref) {
  final repo = ref.watch(calendarRepositoryProvider);
  return repo.getSalesAreasHierarchy();
});

final salesAreaDetailProvider =
    FutureProvider.autoDispose.family<SalesAreaModel, int>((ref, id) {
  final repo = ref.watch(calendarRepositoryProvider);
  return repo.getSalesArea(id);
});
