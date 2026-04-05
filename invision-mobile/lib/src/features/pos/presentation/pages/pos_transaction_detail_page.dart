import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/theme/app_theme.dart';
import '../providers/pos_providers.dart';

class PosTransactionDetailPage extends ConsumerWidget {
  const PosTransactionDetailPage({super.key, required this.transactionId});

  final int transactionId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final txnAsync = ref.watch(posTransactionDetailProvider(transactionId));

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Transaction Detail',
            style: Theme.of(context).textTheme.headlineMedium
                ?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: txnAsync.when(
        data: (txn) => RefreshIndicator(
          onRefresh: () async =>
              ref.invalidate(posTransactionDetailProvider(transactionId)),
          child: ListView(
            padding: const EdgeInsets.all(16),
            children: [
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: AppColors.surfaceContainerLowest,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.primary.withOpacity(0.06),
                      blurRadius: 8, offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(txn.transactionNumber,
                        style: Theme.of(context).textTheme.titleLarge
                            ?.copyWith(color: AppColors.onSurface,
                                fontWeight: FontWeight.w700,
                                fontFamily: 'monospace')),
                    const SizedBox(height: 10),
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: AppColors.surfaceContainerHigh,
                            borderRadius: BorderRadius.circular(100),
                          ),
                          child: Text(txn.type.label,
                              style: const TextStyle(
                                  fontSize: 11,
                                  color: AppColors.onSurfaceVariant,
                                  fontWeight: FontWeight.w600)),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: txn.status.color.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(100),
                            border: Border.all(
                                color: txn.status.color.withOpacity(0.4)),
                          ),
                          child: Text(txn.status.label,
                              style: TextStyle(
                                  fontSize: 11,
                                  color: txn.status.color,
                                  fontWeight: FontWeight.w700)),
                        ),
                      ],
                    ),
                    const Padding(
                        padding: EdgeInsets.symmetric(vertical: 14),
                        child: Divider(color: AppColors.outlineVariant, height: 1)),
                    _InfoRow('Store', txn.storeName ?? '-'),
                    _InfoRow('Terminal', txn.terminalName ?? '-'),
                    _InfoRow('User', txn.userName ?? '-'),
                    _InfoRow('Payment', txn.paymentMethod ?? '-'),
                    if (txn.notes != null) _InfoRow('Notes', txn.notes!),
                    _InfoRow('Date', txn.createdAt ?? '-'),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              Text('Items',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      color: AppColors.onSurface, fontWeight: FontWeight.w700)),
              const SizedBox(height: 8),
              if (txn.items != null)
                ...txn.items!.map(
                  (item) => Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    padding: const EdgeInsets.symmetric(
                        horizontal: 14, vertical: 12),
                    decoration: BoxDecoration(
                      color: AppColors.surfaceContainerLowest,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                          color: AppColors.outlineVariant.withOpacity(0.5)),
                    ),
                    child: Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(item.productName ?? 'Product',
                                  style: const TextStyle(
                                      fontWeight: FontWeight.w700,
                                      color: AppColors.onSurface)),
                              Text(
                                  'Qty: ${item.quantity} × \$${item.unitPrice.toStringAsFixed(2)}',
                                  style: const TextStyle(
                                      fontSize: 12,
                                      color: AppColors.onSurfaceVariant)),
                            ],
                          ),
                        ),
                        Text(
                          '\$${item.lineTotal.toStringAsFixed(2)}',
                          style: const TextStyle(
                              fontWeight: FontWeight.w800,
                              color: AppColors.onSurface),
                        ),
                      ],
                    ),
                  ),
                ),
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppColors.surfaceContainerLowest,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                      color: AppColors.outlineVariant.withOpacity(0.5)),
                ),
                child: Column(
                  children: [
                    _InfoRow('Subtotal',
                        '\$${(txn.subtotal ?? 0).toStringAsFixed(2)}'),
                    _InfoRow('Tax',
                        '\$${(txn.taxAmount ?? 0).toStringAsFixed(2)}'),
                    const Divider(color: AppColors.outlineVariant),
                    _InfoRow(
                      'Total',
                      '\$${txn.totalAmount.toStringAsFixed(2)}',
                      bold: true,
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        loading: () => const Center(
            child: CircularProgressIndicator(color: AppColors.primary)),
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
          Text(label,
              style: const TextStyle(color: AppColors.onSurfaceVariant)),
          Text(
            value,
            style: bold
                ? const TextStyle(
                    fontWeight: FontWeight.w800, color: AppColors.onSurface)
                : const TextStyle(color: AppColors.onSurface),
          ),
        ],
      ),
    );
  }
}
