import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/enums/order_status.dart';
import '../../../../core/theme/app_theme.dart';
import '../../data/models/sales_order_model.dart';
import '../providers/sales_providers.dart';

class MyOrdersPage extends ConsumerStatefulWidget {
  const MyOrdersPage({super.key});

  @override
  ConsumerState<MyOrdersPage> createState() => _MyOrdersPageState();
}

class _MyOrdersPageState extends ConsumerState<MyOrdersPage> {
  String? _statusFilter;

  @override
  Widget build(BuildContext context) {
    final ordersAsync = ref.watch(myOrdersProvider(_statusFilter));

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('My Orders',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
        actions: [
          PopupMenuButton<String?>(
            icon: const Icon(Icons.filter_list_rounded, color: AppColors.primary),
            onSelected: (value) => setState(() => _statusFilter = value),
            itemBuilder: (context) => [
              const PopupMenuItem(value: null, child: Text('All')),
              const PopupMenuItem(value: 'draft', child: Text('Draft')),
              const PopupMenuItem(value: 'confirmed', child: Text('Confirmed')),
              const PopupMenuItem(value: 'delivered', child: Text('Delivered')),
            ],
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => context.push('/sales/create'),
        backgroundColor: AppColors.primary,
        child: const Icon(Icons.add, color: Colors.white),
      ),
      body: ordersAsync.when(
        data: (orders) => orders.isEmpty
            ? Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      width: 64, height: 64,
                      decoration: BoxDecoration(
                          color: AppColors.surfaceContainerHigh,
                          borderRadius: BorderRadius.circular(16)),
                      child: const Icon(Icons.receipt_long_outlined,
                          size: 32, color: AppColors.outline),
                    ),
                    const SizedBox(height: 12),
                    const Text('No orders found',
                        style: TextStyle(color: AppColors.onSurfaceVariant)),
                  ],
                ),
              )
            : RefreshIndicator(
                onRefresh: () async =>
                    ref.invalidate(myOrdersProvider(_statusFilter)),
                child: ListView.builder(
                  itemCount: orders.length,
                  padding: const EdgeInsets.all(12),
                  itemBuilder: (context, index) =>
                      _MyOrderCard(order: orders[index]),
                ),
              ),
        loading: () => const Center(
            child: CircularProgressIndicator(color: AppColors.primary)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}

class _MyOrderCard extends StatelessWidget {
  const _MyOrderCard({required this.order});

  final SalesOrder order;

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
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return GestureDetector(
      onTap: () => context.push('/sales/${order.id}'),
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surfaceContainerLowest,
          borderRadius: BorderRadius.circular(14),
          border: Border(left: BorderSide(color: _statusColor(order.status), width: 3)),
          boxShadow: [
            BoxShadow(color: AppColors.primary.withOpacity(0.04),
                blurRadius: 6, offset: const Offset(0, 2)),
          ],
        ),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(order.orderNumber,
                      style: const TextStyle(
                          fontWeight: FontWeight.w700, color: AppColors.onSurface)),
                  if (order.storeName != null) ...[
                    const SizedBox(height: 2),
                    Text(order.storeName!,
                        style: const TextStyle(
                            fontSize: 12, color: AppColors.onSurfaceVariant)),
                  ],
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Text(
                        '\$${order.totalAmount.toStringAsFixed(2)}',
                        style: const TextStyle(
                            fontWeight: FontWeight.w700,
                            color: AppColors.primary, fontSize: 13),
                      ),
                      if (order.balanceDue != null && order.balanceDue! > 0) ...[
                        const SizedBox(width: 8),
                        Text(
                          'Due: \$${order.balanceDue!.toStringAsFixed(2)}',
                          style: const TextStyle(
                              fontSize: 12, color: AppColors.error),
                        ),
                      ],
                    ],
                  ),
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: _statusBg(order.status),
                borderRadius: BorderRadius.circular(100),
              ),
              child: Text(
                order.status.label,
                style: TextStyle(
                    color: _statusColor(order.status),
                    fontSize: 10, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
