import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../providers/pos_providers.dart';

class StoreInventoryPage extends ConsumerStatefulWidget {
  const StoreInventoryPage({super.key});

  @override
  ConsumerState<StoreInventoryPage> createState() =>
      _StoreInventoryPageState();
}

class _StoreInventoryPageState extends ConsumerState<StoreInventoryPage> {
  final _searchController = TextEditingController();
  InventoryFilter _filter = const InventoryFilter();

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
    final inventoryAsync = ref.watch(storeInventoryProvider(_filter));
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('Store Inventory')),
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
                      hintText: 'Search inventory...',
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
            child: inventoryAsync.when(
              data: (items) => items.isEmpty
                  ? const Center(child: Text('No inventory data found.'))
                  : RefreshIndicator(
                      onRefresh: () async =>
                          ref.invalidate(storeInventoryProvider(_filter)),
                      child: ListView.builder(
                        itemCount: items.length,
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        itemBuilder: (context, index) {
                          final item = items[index];
                          final total = item.totalQuantity ??
                              (item.onShelfQuantity + item.warehouseQuantity);
                          final isLow = total < 10;

                          return Card(
                            margin: const EdgeInsets.only(bottom: 8),
                            child: ListTile(
                              title: Text(item.productName ?? 'Product',
                                  style: theme.textTheme.titleSmall),
                              subtitle: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  if (item.productSku != null)
                                    Text('SKU: ${item.productSku}',
                                        style: theme.textTheme.bodySmall),
                                  if (item.storeName != null)
                                    Text(item.storeName!,
                                        style: theme.textTheme.bodySmall),
                                  Row(
                                    children: [
                                      Text(
                                          'Shelf: ${item.onShelfQuantity}  |  Warehouse: ${item.warehouseQuantity}'),
                                    ],
                                  ),
                                ],
                              ),
                              trailing: Container(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 12, vertical: 4),
                                decoration: BoxDecoration(
                                  color: isLow
                                      ? Colors.red.shade50
                                      : Colors.green.shade50,
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  '$total',
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    color: isLow ? Colors.red : Colors.green,
                                  ),
                                ),
                              ),
                            ),
                          );
                        },
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
