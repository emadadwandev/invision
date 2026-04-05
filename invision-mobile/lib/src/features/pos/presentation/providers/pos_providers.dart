import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/network/api_client.dart';
import '../../data/models/pos_terminal_model.dart';
import '../../data/models/pos_transaction_model.dart';
import '../../data/models/store_inventory_model.dart';
import '../../data/repositories/pos_repository.dart';

final posRepositoryProvider = Provider(
  (ref) => PosRepository(apiClient: ref.watch(apiClientProvider)),
);

// POS Transaction providers
final posTransactionsProvider =
    FutureProvider.autoDispose.family<List<PosTransaction>, PosTransactionFilter>(
  (ref, filter) {
    final repo = ref.watch(posRepositoryProvider);
    return repo.getTransactions(
      search: filter.search,
      type: filter.type,
      status: filter.status,
      storeId: filter.storeId,
    );
  },
);

final posTransactionDetailProvider =
    FutureProvider.autoDispose.family<PosTransaction, int>(
  (ref, id) {
    final repo = ref.watch(posRepositoryProvider);
    return repo.getTransaction(id);
  },
);

final myTransactionsProvider =
    FutureProvider.autoDispose.family<List<PosTransaction>, String?>(
  (ref, type) {
    final repo = ref.watch(posRepositoryProvider);
    return repo.getMyTransactions(type: type);
  },
);

// POS Terminal providers
final posTerminalsProvider =
    FutureProvider.autoDispose.family<List<PosTerminal>, String?>(
  (ref, search) {
    final repo = ref.watch(posRepositoryProvider);
    return repo.getTerminals(search: search);
  },
);

// Store Inventory providers
final storeInventoryProvider =
    FutureProvider.autoDispose.family<List<StoreInventoryItem>, InventoryFilter>(
  (ref, filter) {
    final repo = ref.watch(posRepositoryProvider);
    return repo.getInventory(
      storeId: filter.storeId,
      search: filter.search,
    );
  },
);

// Filter classes
class PosTransactionFilter {
  const PosTransactionFilter({
    this.search,
    this.type,
    this.status,
    this.storeId,
  });

  final String? search;
  final String? type;
  final String? status;
  final int? storeId;

  PosTransactionFilter copyWith({
    String? search,
    String? type,
    String? status,
    int? storeId,
  }) {
    return PosTransactionFilter(
      search: search ?? this.search,
      type: type ?? this.type,
      status: status ?? this.status,
      storeId: storeId ?? this.storeId,
    );
  }

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is PosTransactionFilter &&
          search == other.search &&
          type == other.type &&
          status == other.status &&
          storeId == other.storeId;

  @override
  int get hashCode => Object.hash(search, type, status, storeId);
}

class InventoryFilter {
  const InventoryFilter({this.storeId, this.search});

  final int? storeId;
  final String? search;

  InventoryFilter copyWith({int? storeId, String? search}) {
    return InventoryFilter(
      storeId: storeId ?? this.storeId,
      search: search ?? this.search,
    );
  }

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is InventoryFilter &&
          storeId == other.storeId &&
          search == other.search;

  @override
  int get hashCode => Object.hash(storeId, search);
}
