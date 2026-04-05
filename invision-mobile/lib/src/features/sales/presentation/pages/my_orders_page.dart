import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/enums/order_status.dart';
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
      appBar: AppBar(
        title: const Text('My Orders'),
        actions: [
          PopupMenuButton<String?>(
            icon: const Icon(Icons.filter_list),
            onSelected: (value) {
              setState(() => _statusFilter = value);
            },
            itemBuilder: (context) => [
              const PopupMenuItem(value: null, child: Text('All')),
              const PopupMenuItem(value: 'draft', child: Text('Draft')),
              const PopupMenuItem(
                  value: 'confirmed', child: Text('Confirmed')),
              const PopupMenuItem(
                  value: 'delivered', child: Text('Delivered')),
            ],
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => context.push('/sales/create'),
        child: const Icon(Icons.add),
      ),
      body: ordersAsync.when(
        data: (orders) => orders.isEmpty
            ? const Center(child: Text('No orders found.'))
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
        loading: () => const Center(child: CircularProgressIndicator()),
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
      OrderStatus.draft => Colors.grey,
      OrderStatus.confirmed => Colors.blue,
      OrderStatus.delivered => Colors.green,
      OrderStatus.cancelled => Colors.red,
      OrderStatus.returned => Colors.orange,
    };
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        title: Text(order.orderNumber, style: theme.textTheme.titleSmall),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (order.storeName != null)
              Text(order.storeName!, style: theme.textTheme.bodySmall),
            Row(
              children: [
                Text(
                  '\$${order.totalAmount.toStringAsFixed(2)}',
                  style: theme.textTheme.bodySmall?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),
                if (order.balanceDue != null && order.balanceDue! > 0) ...[
                  const SizedBox(width: 8),
                  Text(
                    'Due: \$${order.balanceDue!.toStringAsFixed(2)}',
                    style: theme.textTheme.bodySmall?.copyWith(
                      color: Colors.red,
                    ),
                  ),
                ],
              ],
            ),
          ],
        ),
        trailing: Chip(
          label: Text(
            order.status.label,
            style: TextStyle(
              color: _statusColor(order.status),
              fontSize: 10,
              fontWeight: FontWeight.bold,
            ),
          ),
          backgroundColor:
              _statusColor(order.status).withValues(alpha: 0.1),
          side: BorderSide.none,
          padding: EdgeInsets.zero,
          visualDensity: VisualDensity.compact,
        ),
        onTap: () => context.push('/sales/${order.id}'),
      ),
    );
  }
}
