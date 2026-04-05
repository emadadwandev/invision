import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/enums/pos_transaction_status.dart';
import '../../../../core/enums/pos_transaction_type.dart';
import '../../../../core/theme/app_theme.dart';
import '../../data/models/pos_transaction_model.dart';
import '../providers/pos_providers.dart';

class PosTransactionListPage extends ConsumerStatefulWidget {
  const PosTransactionListPage({super.key});

  @override
  ConsumerState<PosTransactionListPage> createState() =>
      _PosTransactionListPageState();
}

class _PosTransactionListPageState
    extends ConsumerState<PosTransactionListPage> {
  final _searchController = TextEditingController();
  PosTransactionFilter _filter = const PosTransactionFilter();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _onSearch() {
    setState(() {
      _filter = _filter.copyWith(search: _searchController.text);
    });
  }

  @override
  Widget build(BuildContext context) {
    final txnAsync = ref.watch(posTransactionsProvider(_filter));

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('POS Transactions',
            style: Theme.of(context).textTheme.headlineMedium
                ?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: Column(
        children: [
          Container(
            color: AppColors.surfaceContainerLow,
            padding: const EdgeInsets.all(12),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchController,
                    decoration: InputDecoration(
                      hintText: 'Search transactions...',
                      prefixIcon: const Icon(Icons.search_rounded, size: 20,
                          color: AppColors.outline),
                      isDense: true,
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10)),
                      contentPadding: const EdgeInsets.symmetric(
                          horizontal: 12, vertical: 10),
                    ),
                    onSubmitted: (_) => _onSearch(),
                  ),
                ),
                const SizedBox(width: 8),
                GestureDetector(
                  onTap: _onSearch,
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 16, vertical: 11),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [AppColors.primary, AppColors.primaryContainer],
                      ),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Text('Search',
                        style: TextStyle(
                            color: Colors.white, fontWeight: FontWeight.w700)),
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: txnAsync.when(
              data: (transactions) => transactions.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Container(
                            width: 64, height: 64,
                            decoration: BoxDecoration(
                              color: AppColors.surfaceContainerHigh,
                              borderRadius: BorderRadius.circular(16),
                            ),
                            child: const Icon(Icons.point_of_sale_rounded,
                                size: 28, color: AppColors.onSurfaceVariant),
                          ),
                          const SizedBox(height: 12),
                          const Text('No POS transactions found.',
                              style: TextStyle(
                                  color: AppColors.onSurfaceVariant)),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: () async =>
                          ref.invalidate(posTransactionsProvider(_filter)),
                      child: ListView.builder(
                        itemCount: transactions.length,
                        padding: const EdgeInsets.all(12),
                        itemBuilder: (context, index) =>
                            _TransactionCard(transaction: transactions[index]),
                      ),
                    ),
              loading: () => const Center(
                  child: CircularProgressIndicator(color: AppColors.primary)),
              error: (e, _) => Center(child: Text('Error: $e')),
            ),
          ),
        ],
      ),
    );
  }
}

class _TransactionCard extends StatelessWidget {
  const _TransactionCard({required this.transaction});

  final PosTransaction transaction;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => context.push('/pos/${transaction.id}'),
      child: Container(
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surfaceContainerLowest,
          borderRadius: BorderRadius.circular(14),
          border: Border(left: BorderSide(
            color: transaction.status.color,
            width: 3,
          )),
        ),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(transaction.transactionNumber,
                      style: const TextStyle(
                          fontWeight: FontWeight.w700,
                          color: AppColors.onSurface, fontSize: 13,
                          fontFamily: 'monospace')),
                  if (transaction.storeName != null) ...[
                    const SizedBox(height: 2),
                    Text(transaction.storeName!,
                        style: const TextStyle(
                            fontSize: 12, color: AppColors.onSurfaceVariant)),
                  ],
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      _TypeChip(type: transaction.type),
                      const SizedBox(width: 6),
                      _StatusChip(status: transaction.status),
                    ],
                  ),
                ],
              ),
            ),
            Text(
              '\$${transaction.totalAmount.toStringAsFixed(2)}',
              style: const TextStyle(
                  fontWeight: FontWeight.w800,
                  fontSize: 15, color: AppColors.onSurface),
            ),
          ],
        ),
      ),
    );
  }
}

class _TypeChip extends StatelessWidget {
  const _TypeChip({required this.type});

  final PosTransactionType type;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerHigh,
        borderRadius: BorderRadius.circular(100),
      ),
      child: Text(type.label,
          style: const TextStyle(
              fontSize: 10,
              color: AppColors.onSurfaceVariant,
              fontWeight: FontWeight.w600)),
    );
  }
}

class _StatusChip extends StatelessWidget {
  const _StatusChip({required this.status});

  final PosTransactionStatus status;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
      decoration: BoxDecoration(
        color: status.color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(100),
        border: Border.all(color: status.color.withOpacity(0.4)),
      ),
      child: Text(status.label,
          style: TextStyle(
              fontSize: 10,
              color: status.color,
              fontWeight: FontWeight.w700)),
    );
  }
}
