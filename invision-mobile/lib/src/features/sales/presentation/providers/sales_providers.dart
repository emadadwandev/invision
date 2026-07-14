import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/network/api_client.dart';
import '../../data/models/credit_account_model.dart';
import '../../data/models/payment_model.dart';
import '../../data/models/sales_order_model.dart';
import '../../data/repositories/sales_repository.dart';

final salesRepositoryProvider = Provider(
  (ref) => SalesRepository(apiClient: ref.watch(apiClientProvider)),
);

final salesOrdersProvider =
    FutureProvider.autoDispose.family<List<SalesOrder>, SalesOrderFilter>(
  (ref, filter) {
    final repo = ref.watch(salesRepositoryProvider);
    return repo.getSalesOrders(
      search: filter.search,
      status: filter.status,
    );
  },
);

final salesOrderDetailProvider =
    FutureProvider.autoDispose.family<SalesOrder, int>(
  (ref, id) {
    final repo = ref.watch(salesRepositoryProvider);
    return repo.getSalesOrder(id);
  },
);

final myOrdersProvider =
    FutureProvider.autoDispose.family<List<SalesOrder>, String?>(
  (ref, status) {
    final repo = ref.watch(salesRepositoryProvider);
    return repo.getMyOrders(status: status);
  },
);

final paymentsProvider =
    FutureProvider.autoDispose.family<List<Payment>, PaymentFilter>(
  (ref, filter) {
    final repo = ref.watch(salesRepositoryProvider);
    return repo.getPayments(
      status: filter.status,
      method: filter.method,
    );
  },
);

final creditAccountsProvider =
    FutureProvider.autoDispose.family<List<CreditAccount>, String?>(
  (ref, search) {
    final repo = ref.watch(salesRepositoryProvider);
    return repo.getCreditAccounts(search: search);
  },
);

class SalesOrderFilter {
  const SalesOrderFilter({this.search, this.status});

  final String? search;
  final String? status;

  SalesOrderFilter copyWith({String? search, String? status}) {
    return SalesOrderFilter(
      search: search ?? this.search,
      status: status ?? this.status,
    );
  }

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is SalesOrderFilter &&
          search == other.search &&
          status == other.status;

  @override
  int get hashCode => Object.hash(search, status);
}

class PaymentFilter {
  const PaymentFilter({this.status, this.method});

  final String? status;
  final String? method;

  PaymentFilter copyWith({String? status, String? method}) {
    return PaymentFilter(
      status: status ?? this.status,
      method: method ?? this.method,
    );
  }

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is PaymentFilter &&
          status == other.status &&
          method == other.method;

  @override
  int get hashCode => Object.hash(status, method);
}
