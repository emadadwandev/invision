import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/enums/order_status.dart';
import '../../data/models/sales_order_model.dart';
import '../providers/sales_providers.dart';

class SalesOrderDetailPage extends ConsumerWidget {
  const SalesOrderDetailPage({super.key, required this.orderId});

  final int orderId;

  Color _statusColor(OrderStatus status) {
    return switch (status) {
      OrderStatus.draft => Colors.grey,
      OrderStatus.confirmed => Colors.blue,
      OrderStatus.delivered => Colors.green,
      OrderStatus.cancelled => Colors.red,
      OrderStatus.returned => Colors.orange,
    };
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final orderAsync = ref.watch(salesOrderDetailProvider(orderId));

    return Scaffold(
      appBar: AppBar(title: const Text('Order Details')),
      body: orderAsync.when(
        data: (order) => ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Header
            Row(
              children: [
                Expanded(
                  child: Text(
                    order.orderNumber,
                    style: Theme.of(context).textTheme.headlineSmall,
                  ),
                ),
                Chip(
                  label: Text(
                    order.status.label,
                    style: TextStyle(
                      color: _statusColor(order.status),
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  backgroundColor:
                      _statusColor(order.status).withValues(alpha: 0.1),
                  side: BorderSide.none,
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Info card
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (order.storeName != null)
                      _InfoRow('Store', order.storeName!),
                    if (order.salespersonName != null)
                      _InfoRow('Salesperson', order.salespersonName!),
                    if (order.createdAt != null)
                      _InfoRow('Created', order.createdAt!),
                    if (order.deliveredAt != null)
                      _InfoRow('Delivered', order.deliveredAt!),
                    if (order.notes != null && order.notes!.isNotEmpty)
                      _InfoRow('Notes', order.notes!),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Items
            if (order.items != null && order.items!.isNotEmpty) ...[
              Text('Items',
                  style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 8),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(12),
                  child: Column(
                    children: [
                      for (final item in order.items!)
                        Padding(
                          padding:
                              const EdgeInsets.symmetric(vertical: 4),
                          child: Row(
                            children: [
                              Expanded(
                                child: Text(
                                  item.productName ?? 'Product #${item.productId}',
                                  style: Theme.of(context)
                                      .textTheme
                                      .bodyMedium,
                                ),
                              ),
                              Text('x${item.quantity}'),
                              const SizedBox(width: 16),
                              Text(
                                '\$${item.lineTotal.toStringAsFixed(2)}',
                                style: const TextStyle(
                                    fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                        ),
                      const Divider(),
                      _InfoRow('Subtotal',
                          '\$${order.subtotal.toStringAsFixed(2)}'),
                      if (order.discountAmount != null &&
                          order.discountAmount! > 0)
                        _InfoRow('Discount',
                            '-\$${order.discountAmount!.toStringAsFixed(2)}'),
                      if (order.taxAmount != null && order.taxAmount! > 0)
                        _InfoRow('Tax',
                            '\$${order.taxAmount!.toStringAsFixed(2)}'),
                      _InfoRow('Total',
                          '\$${order.totalAmount.toStringAsFixed(2)}'),
                      if (order.totalPaid != null)
                        _InfoRow('Paid',
                            '\$${order.totalPaid!.toStringAsFixed(2)}'),
                      if (order.balanceDue != null)
                        _InfoRow('Balance Due',
                            '\$${order.balanceDue!.toStringAsFixed(2)}'),
                    ],
                  ),
                ),
              ),
            ],
            const SizedBox(height: 16),

            // Action buttons
            if (order.status == OrderStatus.draft) ...[
              FilledButton.icon(
                onPressed: () => _confirmOrder(context, ref, order),
                icon: const Icon(Icons.check),
                label: const Text('Confirm Order'),
              ),
              const SizedBox(height: 8),
            ],
            if (order.status == OrderStatus.confirmed) ...[
              FilledButton.icon(
                onPressed: () => _deliverOrder(context, ref, order),
                icon: const Icon(Icons.local_shipping),
                label: const Text('Mark Delivered'),
              ),
              const SizedBox(height: 8),
            ],
            if (order.status == OrderStatus.draft ||
                order.status == OrderStatus.confirmed) ...[
              OutlinedButton.icon(
                onPressed: () => _cancelOrder(context, ref, order),
                icon: const Icon(Icons.cancel, color: Colors.red),
                label: const Text('Cancel Order',
                    style: TextStyle(color: Colors.red)),
              ),
            ],
          ],
        ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }

  Future<void> _confirmOrder(
      BuildContext context, WidgetRef ref, SalesOrder order) async {
    final repo = ref.read(salesRepositoryProvider);
    await repo.confirmOrder(order.id);
    ref.invalidate(salesOrderDetailProvider(orderId));
  }

  Future<void> _deliverOrder(
      BuildContext context, WidgetRef ref, SalesOrder order) async {
    final repo = ref.read(salesRepositoryProvider);
    await repo.deliverOrder(order.id);
    ref.invalidate(salesOrderDetailProvider(orderId));
  }

  Future<void> _cancelOrder(
      BuildContext context, WidgetRef ref, SalesOrder order) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Cancel Order'),
        content:
            const Text('Are you sure you want to cancel this order?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('No'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Yes, Cancel'),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      final repo = ref.read(salesRepositoryProvider);
      await repo.cancelOrder(order.id);
      ref.invalidate(salesOrderDetailProvider(orderId));
    }
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow(this.label, this.value);

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: Theme.of(context).textTheme.bodySmall),
          Text(value, style: Theme.of(context).textTheme.bodyMedium),
        ],
      ),
    );
  }
}
