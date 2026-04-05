import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/enums/pos_transaction_status.dart';
import '../../../../core/enums/pos_transaction_type.dart';
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
      appBar: AppBar(title: const Text('POS Transactions')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchController,
                    decoration: const InputDecoration(
                      hintText: 'Search transactions...',
                      prefixIcon: Icon(Icons.search),
                      border: OutlineInputBorder(),
                      isDense: true,
                    ),
                    onSubmitted: (_) => _onSearch(),
                  ),
                ),
                const SizedBox(width: 8),
                FilledButton(
                  onPressed: _onSearch,
                  child: const Text('Search'),
                ),
              ],
            ),
          ),
          Expanded(
            child: txnAsync.when(
              data: (transactions) => transactions.isEmpty
                  ? const Center(child: Text('No POS transactions found.'))
                  : RefreshIndicator(
                      onRefresh: () async =>
                          ref.invalidate(posTransactionsProvider(_filter)),
                      child: ListView.builder(
                        itemCount: transactions.length,
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        itemBuilder: (context, index) =>
                            _TransactionCard(transaction: transactions[index]),
                      ),
                    ),
              loading: () =>
                  const Center(child: CircularProgressIndicator()),
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
    final theme = Theme.of(context);

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        onTap: () => context.push('/pos/${transaction.id}'),
        title: Text(transaction.transactionNumber,
            style: theme.textTheme.titleSmall),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (transaction.storeName != null)
              Text(transaction.storeName!,
                  style: theme.textTheme.bodySmall),
            Row(
              children: [
                _TypeChip(type: transaction.type),
                const SizedBox(width: 4),
                _StatusChip(status: transaction.status),
              ],
            ),
          ],
        ),
        trailing: Text(
          '\$${transaction.totalAmount.toStringAsFixed(2)}',
          style: theme.textTheme.titleSmall?.copyWith(
            fontWeight: FontWeight.bold,
          ),
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
    return Chip(
      label: Text(type.label, style: const TextStyle(fontSize: 11)),
      padding: EdgeInsets.zero,
      materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
      visualDensity: VisualDensity.compact,
    );
  }
}

class _StatusChip extends StatelessWidget {
  const _StatusChip({required this.status});

  final PosTransactionStatus status;

  @override
  Widget build(BuildContext context) {
    return Chip(
      label: Text(status.label,
          style: TextStyle(fontSize: 11, color: status.color)),
      padding: EdgeInsets.zero,
      materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
      visualDensity: VisualDensity.compact,
      side: BorderSide(color: status.color),
    );
  }
}
