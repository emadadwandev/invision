import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../providers/pos_providers.dart';

class PosTransactionDetailPage extends ConsumerWidget {
  const PosTransactionDetailPage({super.key, required this.transactionId});

  final int transactionId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final txnAsync = ref.watch(posTransactionDetailProvider(transactionId));
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('Transaction Detail')),
      body: txnAsync.when(
        data: (txn) => RefreshIndicator(
          onRefresh: () async =>
              ref.invalidate(posTransactionDetailProvider(transactionId)),
          child: ListView(
            padding: const EdgeInsets.all(16),
            children: [
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(txn.transactionNumber,
                          style: theme.textTheme.titleLarge),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          Chip(label: Text(txn.type.label)),
                          const SizedBox(width: 8),
                          Chip(
                            label: Text(txn.status.label,
                                style: TextStyle(color: txn.status.color)),
                            side: BorderSide(color: txn.status.color),
                          ),
                        ],
                      ),
                      const Divider(height: 24),
                      _InfoRow('Store', txn.storeName ?? '-'),
                      _InfoRow('Terminal', txn.terminalName ?? '-'),
                      _InfoRow('User', txn.userName ?? '-'),
                      _InfoRow('Payment', txn.paymentMethod ?? '-'),
                      if (txn.notes != null) _InfoRow('Notes', txn.notes!),
                      _InfoRow('Date', txn.createdAt ?? '-'),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Text('Items', style: theme.textTheme.titleMedium),
              const SizedBox(height: 8),
              if (txn.items != null)
                ...txn.items!.map(
                  (item) => Card(
                    margin: const EdgeInsets.only(bottom: 8),
                    child: ListTile(
                      title: Text(item.productName ?? 'Product'),
                      subtitle: Text(
                          'Qty: ${item.quantity} × \$${item.unitPrice.toStringAsFixed(2)}'),
                      trailing: Text(
                        '\$${item.lineTotal.toStringAsFixed(2)}',
                        style: theme.textTheme.titleSmall?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ),
                ),
              const SizedBox(height: 16),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    children: [
                      _InfoRow('Subtotal',
                          '\$${(txn.subtotal ?? 0).toStringAsFixed(2)}'),
                      _InfoRow('Tax',
                          '\$${(txn.taxAmount ?? 0).toStringAsFixed(2)}'),
                      const Divider(),
                      _InfoRow(
                        'Total',
                        '\$${txn.totalAmount.toStringAsFixed(2)}',
                        bold: true,
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow(this.label, this.value, {this.bold = false});

  final String label;
  final String value;
  final bool bold;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: Theme.of(context).textTheme.bodyMedium),
          Text(
            value,
            style: bold
                ? Theme.of(context)
                    .textTheme
                    .titleSmall
                    ?.copyWith(fontWeight: FontWeight.bold)
                : null,
          ),
        ],
      ),
    );
  }
}
