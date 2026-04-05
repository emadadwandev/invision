import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

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
      appBar: AppBar(title: const Text('Sales Inquiry')),
      body: Column(
        children: [
          Container(
            color: Colors.grey.shade50,
            padding: const EdgeInsets.all(12),
            child: Row(
              children: [
                Expanded(
                  flex: 2,
                  child: TextField(
                    controller: _searchCtrl,
                    decoration: InputDecoration(
                      hintText: 'Order #...',
                      prefixIcon: const Icon(Icons.search, size: 20),
                      isDense: true,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    ),
                    onSubmitted: (_) => setState(() {}),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: DropdownButtonFormField<String>(
                    key: ValueKey(_status),
                    initialValue: _status,
                    decoration: const InputDecoration(labelText: 'Status', isDense: true, border: OutlineInputBorder()),
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
              loading: () => const Center(child: CircularProgressIndicator()),
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
          'delivered' => Colors.green,
          'cancelled' => Colors.red,
          'confirmed' => Colors.blue,
          'pending' => Colors.orange,
          _ => Colors.grey,
        };
        return Card(
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Text(o.orderNumber, style: const TextStyle(fontWeight: FontWeight.w600, fontFamily: 'monospace', fontSize: 13)),
                    const Spacer(),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: statusColor.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(o.status.toUpperCase(), style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: statusColor)),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Text('${o.storeName ?? '-'}  ·  ${o.salesperson ?? '-'}', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                Text(o.createdAt, style: TextStyle(fontSize: 11, color: Colors.grey.shade400)),
                const Divider(height: 16),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    _Val(label: 'Total', value: _currency.format(o.total)),
                    _Val(label: 'Paid', value: _currency.format(o.paid), color: Colors.green),
                    _Val(label: 'Balance', value: _currency.format(o.balanceDue), color: o.balanceDue > 0 ? Colors.red : null),
                  ],
                ),
              ],
            ),
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
        Text(label, style: TextStyle(fontSize: 10, color: Colors.grey.shade500)),
        const SizedBox(height: 2),
        Text(value, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: color)),
      ],
    );
  }
}
