import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/enums/order_status.dart';
import '../../data/models/sales_order_model.dart';
import '../providers/sales_providers.dart';

class SalesOrderListPage extends ConsumerStatefulWidget {
  const SalesOrderListPage({super.key});

  @override
  ConsumerState<SalesOrderListPage> createState() =>
      _SalesOrderListPageState();
}

class _SalesOrderListPageState extends ConsumerState<SalesOrderListPage> {
  final _searchController = TextEditingController();
  SalesOrderFilter _filter = const SalesOrderFilter();

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
    final ordersAsync = ref.watch(salesOrdersProvider(_filter));

    return Scaffold(
      appBar: AppBar(title: const Text('Sales Orders')),
      floatingActionButton: FloatingActionButton(
        onPressed: () => context.push('/sales/create'),
        child: const Icon(Icons.add),
      ),
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
                      hintText: 'Search orders...',
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
            child: ordersAsync.when(
              data: (orders) => orders.isEmpty
                  ? const Center(child: Text('No sales orders found.'))
                  : RefreshIndicator(
                      onRefresh: () async =>
                          ref.invalidate(salesOrdersProvider(_filter)),
                      child: ListView.builder(
                        itemCount: orders.length,
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        itemBuilder: (context, index) =>
                            _OrderCard(order: orders[index]),
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

class _OrderCard extends StatelessWidget {
  const _OrderCard({required this.order});

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
            Text(
              '\$${order.totalAmount.toStringAsFixed(2)}',
              style: theme.textTheme.bodySmall?.copyWith(
                fontWeight: FontWeight.bold,
              ),
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
