import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/enums/order_status.dart';
import '../../../../core/theme/app_theme.dart';
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
    final tt = Theme.of(context).textTheme;
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Sales Orders', style: tt.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => context.push('/sales/create'),
        backgroundColor: AppColors.primary,
        child: const Icon(Icons.add_rounded, color: Colors.white),
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchController,
                    onSubmitted: (_) => _onSearch(),
                    decoration: const InputDecoration(
                      hintText: 'Search orders...',
                      prefixIcon: Icon(Icons.search_rounded, color: AppColors.outline, size: 20),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                GestureDetector(
                  onTap: _onSearch,
                  child: Container(
                    width: 48, height: 48,
                    decoration: BoxDecoration(
                      color: AppColors.primary,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.search_rounded, color: Colors.white, size: 22),
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: ordersAsync.when(
              data: (orders) => orders.isEmpty
                  ? Center(
                      child: Column(mainAxisSize: MainAxisSize.min, children: [
                        Container(
                          width: 64, height: 64,
                          decoration: BoxDecoration(
                            color: AppColors.surfaceContainerHigh,
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: const Icon(Icons.receipt_long_rounded, size: 32, color: AppColors.outline),
                        ),
                        const SizedBox(height: 12),
                        Text('No sales orders found.',
                            style: tt.bodyLarge?.copyWith(color: AppColors.onSurfaceVariant)),
                      ]),
                    )
                  : RefreshIndicator(
                      color: AppColors.primary,
                      onRefresh: () async => ref.invalidate(salesOrdersProvider(_filter)),
                      child: ListView.builder(
                        itemCount: orders.length,
                        padding: const EdgeInsets.fromLTRB(16, 4, 16, 100),
                        itemBuilder: (context, index) => _OrderCard(order: orders[index]),
                      ),
                    ),
              loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
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
    final tt = Theme.of(context).textTheme;
    return GestureDetector(
      onTap: () => context.push('/sales/${order.id}'),
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surfaceContainerLowest,
          borderRadius: BorderRadius.circular(14),
          border: Border(
            left: BorderSide(color: _statusColor(order.status), width: 3),
          ),
        ),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(order.orderNumber,
                      style: tt.titleSmall?.copyWith(
                          color: AppColors.primary, fontWeight: FontWeight.w700)),
                  if (order.storeName != null)
                    Padding(
                      padding: const EdgeInsets.only(top: 2),
                      child: Text(order.storeName!,
                          style: tt.bodySmall?.copyWith(color: AppColors.onSurface)),
                    ),
                  Padding(
                    padding: const EdgeInsets.only(top: 4),
                    child: Text('\$${order.totalAmount.toStringAsFixed(2)}',
                        style: tt.bodyMedium?.copyWith(
                            color: AppColors.onSurface, fontWeight: FontWeight.w700)),
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
                    fontSize: 10, fontWeight: FontWeight.w700),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
