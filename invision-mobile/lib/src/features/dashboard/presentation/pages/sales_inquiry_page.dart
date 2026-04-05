import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../../core/theme/app_theme.dart';
import '../../data/models/inquiry_models.dart';
import '../providers/dashboard_providers.dart';

final _currency = NumberFormat.currency(symbol: '\$', decimalDigits: 2);

class SalesInquiryPage extends ConsumerStatefulWidget {
  const SalesInquiryPage({super.key});

  @override
  ConsumerState<SalesInquiryPage> createState() => _SalesInquiryPageState();
}

class _SalesInquiryPageState extends ConsumerState<SalesInquiryPage> {
  final _searchCtrl = TextEditingController();
  String? _status;

  SalesInquiryFilter get _filter =>
      SalesInquiryFilter(search: _searchCtrl.text, status: _status);

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final orders = ref.watch(salesInquiryProvider(_filter));

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Sales Inquiry',
            style: Theme.of(context).textTheme.headlineMedium
                ?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: Column(
        children: [
          Container(
            color: AppColors.surfaceContainerLow,
            padding: const EdgeInsets.all(12),
            child: Row(
              children: [
                Expanded(
                  flex: 2,
                  child: TextField(
                    controller: _searchCtrl,
                    decoration: InputDecoration(
                      hintText: 'Order #...',
                      prefixIcon: const Icon(Icons.search_rounded, size: 20,
                          color: AppColors.outline),
                      isDense: true,
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10)),
                      contentPadding: const EdgeInsets.symmetric(
                          horizontal: 12, vertical: 10),
                    ),
                    onSubmitted: (_) => setState(() {}),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: DropdownButtonFormField<String>(
                    key: ValueKey(_status),
                    initialValue: _status,
                    decoration: const InputDecoration(
                        labelText: 'Status', isDense: true),
                    items: const [
                      DropdownMenuItem(value: null, child: Text('All')),
                      DropdownMenuItem(value: 'draft', child: Text('Draft')),
                      DropdownMenuItem(value: 'pending', child: Text('Pending')),
                      DropdownMenuItem(value: 'confirmed', child: Text('Confirmed')),
                      DropdownMenuItem(value: 'delivered', child: Text('Delivered')),
                      DropdownMenuItem(value: 'cancelled', child: Text('Cancelled')),
                    ],
                    onChanged: (v) => setState(() => _status = v),
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: orders.when(
              data: (list) => _OrderList(orders: list),
              loading: () => const Center(
                  child: CircularProgressIndicator(color: AppColors.primary)),
              error: (e, _) => Center(child: Text('Error: $e')),
            ),
          ),
        ],
      ),
    );
  }
}

class _OrderList extends StatelessWidget {
  const _OrderList({required this.orders});
  final List<SalesInquiryItem> orders;

  @override
  Widget build(BuildContext context) {
    if (orders.isEmpty) {
      return const Center(child: Text('No orders found.'));
    }
    return ListView.separated(
      padding: const EdgeInsets.all(12),
      itemCount: orders.length,
      separatorBuilder: (_, _) => const SizedBox(height: 8),
      itemBuilder: (_, i) {
        final o = orders[i];
        final statusColor = switch (o.status) {
          'delivered' => AppColors.secondary,
          'cancelled' => AppColors.error,
          'confirmed' => AppColors.primaryContainer,
          'pending' => AppColors.tertiary,
          _ => AppColors.outline,
        };
        final statusBg = switch (o.status) {
          'delivered' => AppColors.secondaryContainer,
          'cancelled' => AppColors.errorContainer,
          'confirmed' => AppColors.surfaceContainerLow,
          'pending' => AppColors.tertiaryContainer.withOpacity(0.3),
          _ => AppColors.surfaceContainerHigh,
        };
        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: AppColors.surfaceContainerLowest,
            borderRadius: BorderRadius.circular(14),
            border: Border(left: BorderSide(color: statusColor, width: 3)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Text(o.orderNumber,
                      style: const TextStyle(
                          fontWeight: FontWeight.w700,
                          fontFamily: 'monospace',
                          fontSize: 13, color: AppColors.onSurface)),
                  const Spacer(),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                        color: statusBg, borderRadius: BorderRadius.circular(100)),
                    child: Text(o.status.toUpperCase(),
                        style: TextStyle(
                            fontSize: 10, fontWeight: FontWeight.w700,
                            color: statusColor)),
                  ),
                ],
              ),
              const SizedBox(height: 4),
              Text('${o.storeName ?? '-'}  ·  ${o.salesperson ?? '-'}',
                  style: const TextStyle(
                      fontSize: 12, color: AppColors.onSurfaceVariant)),
              Text(o.createdAt,
                  style: const TextStyle(
                      fontSize: 11, color: AppColors.outline)),
              const Padding(
                  padding: EdgeInsets.symmetric(vertical: 10),
                  child: Divider(color: AppColors.outlineVariant, height: 1)),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  _Val(label: 'Total', value: _currency.format(o.total)),
                  _Val(label: 'Paid', value: _currency.format(o.paid),
                      color: AppColors.secondary),
                  _Val(label: 'Balance',
                      value: _currency.format(o.balanceDue),
                      color: o.balanceDue > 0 ? AppColors.error : null),
                ],
              ),
            ],
          ),
        );
      },
    );
  }
}

class _Val extends StatelessWidget {
  const _Val({required this.label, required this.value, this.color});
  final String label;
  final String value;
  final Color? color;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(label,
            style: const TextStyle(fontSize: 10, color: AppColors.outline)),
        const SizedBox(height: 2),
        Text(value,
            style: TextStyle(
                fontSize: 12, fontWeight: FontWeight.w700, color: color
                    ?? AppColors.onSurface)),
      ],
    );
  }
}
