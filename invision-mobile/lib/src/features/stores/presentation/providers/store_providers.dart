import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/network/api_client.dart';
import '../../data/models/store_model.dart';
import '../../data/repositories/store_repository.dart';

final storeRepositoryProvider = Provider(
  (ref) => StoreRepository(apiClient: ref.watch(apiClientProvider)),
);

final storesProvider =
    FutureProvider.autoDispose.family<List<Store>, StoreFilter>(
  (ref, filter) {
    final repo = ref.watch(storeRepositoryProvider);
    return repo.getStores(
      search: filter.search,
      category: filter.category,
      rank: filter.rank,
    );
  },
);

final storeDetailProvider = FutureProvider.autoDispose.family<Store, int>(
  (ref, id) {
    final repo = ref.watch(storeRepositoryProvider);
    return repo.getStore(id);
  },
);

class StoreFilter {
  const StoreFilter({this.search, this.category, this.rank});

  final String? search;
  final String? category;
  final String? rank;

  StoreFilter copyWith({String? search, String? category, String? rank}) {
    return StoreFilter(
      search: search ?? this.search,
      category: category ?? this.category,
      rank: rank ?? this.rank,
    );
  }

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is StoreFilter &&
          search == other.search &&
          category == other.category &&
          rank == other.rank;

  @override
  int get hashCode => Object.hash(search, category, rank);
}
