import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../data/models/pos_transaction_model.dart';
import '../providers/pos_providers.dart';

class MyTransactionsPage extends ConsumerStatefulWidget {
  const MyTransactionsPage({super.key});

  @override
  ConsumerState<MyTransactionsPage> createState() =>
      _MyTransactionsPageState();
}

class _MyTransactionsPageState extends ConsumerState<MyTransactionsPage> {
  String? _typeFilter;

  @override
  Widget build(BuildContext context) {
    final txnAsync = ref.watch(myTransactionsProvider(_typeFilter));

    return Scaffold(
      appBar: AppBar(
        title: const Text('My POS Transactions'),
        actions: [
          PopupMenuButton<String?>(
            icon: const Icon(Icons.filter_list),
            onSelected: (value) => setState(() => _typeFilter = value),
            itemBuilder: (context) => [
              const PopupMenuItem(value: null, child: Text('All Types')),
              const PopupMenuItem(
                  value: 'sell_out', child: Text('Sell Out')),
              const PopupMenuItem(
                  value: 'sell_through', child: Text('Sell Through')),
              const PopupMenuItem(
                  value: 'return', child: Text('Return')),
            ],
          ),
        ],
      ),
      body: txnAsync.when(
        data: (transactions) => transactions.isEmpty
            ? const Center(child: Text('No transactions yet.'))
            : RefreshIndicator(
                onRefresh: () async =>
                    ref.invalidate(myTransactionsProvider(_typeFilter)),
                child: ListView.builder(
                  itemCount: transactions.length,
                  padding: const EdgeInsets.all(12),
                  itemBuilder: (context, index) =>
                      _MyTxnCard(transaction: transactions[index]),
                ),
              ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}

class _MyTxnCard extends StatelessWidget {
  const _MyTxnCard({required this.transaction});

  final PosTransaction transaction;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
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
                Chip(
                  label: Text(transaction.type.label,
                      style: const TextStyle(fontSize: 11)),
                  padding: EdgeInsets.zero,
                  materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  visualDensity: VisualDensity.compact,
                ),
                const SizedBox(width: 4),
                Chip(
                  label: Text(transaction.status.label,
                      style: TextStyle(
                          fontSize: 11, color: transaction.status.color)),
                  padding: EdgeInsets.zero,
                  materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  visualDensity: VisualDensity.compact,
                  side: BorderSide(color: transaction.status.color),
                ),
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
