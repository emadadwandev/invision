import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../../core/theme/app_theme.dart';
import '../../data/models/inquiry_models.dart';
import '../providers/dashboard_providers.dart';

final _currency = NumberFormat.currency(symbol: '\$', decimalDigits: 2);

class StoreInquiryPage extends ConsumerStatefulWidget {
  const StoreInquiryPage({super.key});

  @override
  ConsumerState<StoreInquiryPage> createState() => _StoreInquiryPageState();
}

class _StoreInquiryPageState extends ConsumerState<StoreInquiryPage> {
  final _searchCtrl = TextEditingController();
  String? _category;
  String? _rank;

  StoreInquiryFilter get _filter =>
      StoreInquiryFilter(search: _searchCtrl.text, category: _category, rank: _rank);

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final stores = ref.watch(storeInquiryProvider(_filter));

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Store Inquiry',
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
            child: Column(
              children: [
                TextField(
                  controller: _searchCtrl,
                  decoration: InputDecoration(
                    hintText: 'Search by name or code...',
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
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        key: ValueKey(_category),
                        initialValue: _category,
                        decoration: const InputDecoration(
                            labelText: 'Category', isDense: true),
                        items: const [
                          DropdownMenuItem(value: null, child: Text('All')),
                          DropdownMenuItem(value: 'grocery', child: Text('Grocery')),
                          DropdownMenuItem(value: 'pharmacy', child: Text('Pharmacy')),
                          DropdownMenuItem(value: 'convenience', child: Text('Convenience')),
                          DropdownMenuItem(value: 'supermarket', child: Text('Supermarket')),
                        ],
                        onChanged: (v) => setState(() => _category = v),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        key: ValueKey(_rank),
                        initialValue: _rank,
                        decoration: const InputDecoration(
                            labelText: 'Rank', isDense: true),
                        items: const [
                          DropdownMenuItem(value: null, child: Text('All')),
                          DropdownMenuItem(value: 'gold', child: Text('Gold')),
                          DropdownMenuItem(value: 'silver', child: Text('Silver')),
                          DropdownMenuItem(value: 'bronze', child: Text('Bronze')),
                        ],
                        onChanged: (v) => setState(() => _rank = v),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          Expanded(
            child: stores.when(
              data: (list) => _StoreList(stores: list),
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

class _StoreList extends StatelessWidget {
  const _StoreList({required this.stores});
  final List<StoreInquiryItem> stores;

  @override
  Widget build(BuildContext context) {
    if (stores.isEmpty) {
      return const Center(child: Text('No stores found.'));
    }
    return ListView.separated(
      padding: const EdgeInsets.all(12),
      itemCount: stores.length,
      separatorBuilder: (_, _) => const SizedBox(height: 8),
      itemBuilder: (_, i) {
        final s = stores[i];
        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: AppColors.surfaceContainerLowest,
            borderRadius: BorderRadius.circular(14),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(s.name,
                        style: const TextStyle(
                            fontWeight: FontWeight.w700,
                            color: AppColors.onSurface, fontSize: 14)),
                  ),
                  Text(s.code,
                      style: const TextStyle(
                          fontSize: 11,
                          color: AppColors.outline,
                          fontFamily: 'monospace')),
                ],
              ),
              const SizedBox(height: 4),
              if (s.area != null || s.category != null)
                Text([s.category, s.rank, s.area].whereType<String>().join(' · '),
                    style: const TextStyle(
                        fontSize: 12, color: AppColors.onSurfaceVariant)),
              const Padding(
                  padding: EdgeInsets.symmetric(vertical: 8),
                  child: Divider(color: AppColors.outlineVariant, height: 1)),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  _Metric(label: 'Orders', value: '${s.orderCount}'),
                  _Metric(label: 'Sales', value: _currency.format(s.totalSales)),
                  _Metric(label: 'Stock', value: '${s.stockQuantity}'),
                  if (s.creditBalance != null)
                    _Metric(label: 'Credit Bal',
                        value: _currency.format(s.creditBalance)),
                ],
              ),
            ],
          ),
        );
      },
    );
  }
}

class _Metric extends StatelessWidget {
  const _Metric({required this.label, required this.value});
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(label,
            style: const TextStyle(fontSize: 10, color: AppColors.outline)),
        const SizedBox(height: 2),
        Text(value,
            style: const TextStyle(
                fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.onSurface)),
      ],
    );
  }
}
