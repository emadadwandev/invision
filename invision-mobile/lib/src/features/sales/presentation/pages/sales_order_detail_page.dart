import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/enums/order_status.dart';
import '../../../../core/theme/app_theme.dart';
import '../../data/models/sales_order_model.dart';
import '../providers/sales_providers.dart';

class SalesOrderDetailPage extends ConsumerWidget {
  const SalesOrderDetailPage({super.key, required this.orderId});

  final int orderId;

  Color _statusColor(OrderStatus status) {
    return switch (status) {
      OrderStatus.draft => AppColors.onSurfaceVariant,
      OrderStatus.confirmed => AppColors.primaryContainer,
      OrderStatus.delivered => AppColors.secondary,
      OrderStatus.cancelled => AppColors.error,
      OrderStatus.returned => AppColors.tertiary,
    };
  }

  Color _statusBg(OrderStatus status) {
    return switch (status) {
      OrderStatus.draft => AppColors.surfaceContainerHigh,
      OrderStatus.confirmed => AppColors.surfaceContainerLow,
      OrderStatus.delivered => AppColors.secondaryContainer,
      OrderStatus.cancelled => AppColors.errorContainer,
      OrderStatus.returned => AppColors.tertiaryContainer.withOpacity(0.3),
    };
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final orderAsync = ref.watch(salesOrderDetailProvider(orderId));

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Order Details',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: orderAsync.when(
        data: (order) => ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Header gradient card
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [AppColors.primary, AppColors.primaryContainer],
                  begin: Alignment.topLeft, end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: Text(order.orderNumber,
                        style: Theme.of(context)
                            .textTheme.headlineSmall?.copyWith(color: Colors.white)),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.25),
                      borderRadius: BorderRadius.circular(100),
                    ),
                    child: Text(order.status.label,
                        style: const TextStyle(
                            color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700)),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 14),

            // Info card
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppColors.surfaceContainerLowest,
                borderRadius: BorderRadius.circular(16),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Order Info',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(color: AppColors.onSurface)),
                  const SizedBox(height: 12),
                  Divider(color: AppColors.outlineVariant.withOpacity(0.5), height: 1),
                  const SizedBox(height: 12),
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
            const SizedBox(height: 14),

            // Items
            if (order.items != null && order.items!.isNotEmpty) ...[
              Text('Items',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(color: AppColors.onSurface)),
              const SizedBox(height: 8),
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppColors.surfaceContainerLowest,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Column(
                  children: [
                    for (final item in order.items!)
                      Padding(
                        padding: const EdgeInsets.symmetric(vertical: 5),
                        child: Row(
                          children: [
                            Expanded(
                              child: Text(
                                item.productName ?? 'Product #${item.productId}',
                                style: Theme.of(context).textTheme.bodyMedium
                                    ?.copyWith(color: AppColors.onSurface),
                              ),
                            ),
                            Text('x${item.quantity}',
                                style: Theme.of(context).textTheme.bodySmall
                                    ?.copyWith(color: AppColors.onSurfaceVariant)),
                            const SizedBox(width: 16),
                            Text(
                              '\$${item.lineTotal.toStringAsFixed(2)}',
                              style: Theme.of(context).textTheme.bodyMedium
                                  ?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w700),
                            ),
                          ],
                        ),
                      ),
                    Divider(color: AppColors.outlineVariant.withOpacity(0.5), height: 20),
                    _InfoRow('Subtotal', '\$${order.subtotal.toStringAsFixed(2)}'),
                    if (order.discountAmount != null && order.discountAmount! > 0)
                      _InfoRow('Discount', '-\$${order.discountAmount!.toStringAsFixed(2)}'),
                    if (order.taxAmount != null && order.taxAmount! > 0)
                      _InfoRow('Tax', '\$${order.taxAmount!.toStringAsFixed(2)}'),
                    _InfoRow('Total', '\$${order.totalAmount.toStringAsFixed(2)}'),
                    if (order.totalPaid != null) _InfoRow('Paid', '\$${order.totalPaid!.toStringAsFixed(2)}'),
                    if (order.balanceDue != null) _InfoRow('Balance Due', '\$${order.balanceDue!.toStringAsFixed(2)}'),
                  ],
                ),
              )
            ],
            const SizedBox(height: 14),

            // Action buttons
            if (order.status == OrderStatus.draft) ...[
              GestureDetector(
                onTap: () => _confirmOrder(context, ref, order),
                child: Container(
                  width: double.infinity, height: 52,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [AppColors.primary, AppColors.primaryContainer],
                    ),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  alignment: Alignment.center,
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.check_rounded, color: Colors.white, size: 18),
                      SizedBox(width: 8),
                      Text('Confirm Order',
                          style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 10),
            ],
            if (order.status == OrderStatus.confirmed) ...[
              GestureDetector(
                onTap: () => _deliverOrder(context, ref, order),
                child: Container(
                  width: double.infinity, height: 52,
                  decoration: BoxDecoration(
                    color: AppColors.secondary,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  alignment: Alignment.center,
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.local_shipping_rounded, color: Colors.white, size: 18),
                      SizedBox(width: 8),
                      Text('Mark Delivered',
                          style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 10),
            ],
            if (order.status == OrderStatus.draft ||
                order.status == OrderStatus.confirmed) ...[
              GestureDetector(
                onTap: () => _cancelOrder(context, ref, order),
                child: Container(
                  width: double.infinity, height: 52,
                  decoration: BoxDecoration(
                    color: AppColors.errorContainer,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  alignment: Alignment.center,
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.cancel_rounded, color: AppColors.error, size: 18),
                      SizedBox(width: 8),
                      Text('Cancel Order',
                          style: TextStyle(color: AppColors.error, fontWeight: FontWeight.w700)),
                    ],
                  ),
                ),
              ),
            ],
          ],
        ),
        loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
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
    final tt = Theme.of(context).textTheme;
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: tt.bodySmall?.copyWith(color: AppColors.onSurfaceVariant)),
          Text(value,
              style: tt.bodyMedium?.copyWith(color: AppColors.onSurface)),
        ],
      ),
    );
  }
}
