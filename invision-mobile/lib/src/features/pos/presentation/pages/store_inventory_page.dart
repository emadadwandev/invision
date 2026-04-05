import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/theme/app_theme.dart';
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
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Store Inventory',
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
                  child: TextField(
                    controller: _searchController,
                    decoration: InputDecoration(
                      hintText: 'Search inventory...',
                      prefixIcon: const Icon(Icons.search_rounded, size: 20,
                          color: AppColors.outline),
                      isDense: true,
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10)),
                      contentPadding: const EdgeInsets.symmetric(
                          horizontal: 12, vertical: 10),
                    ),
                    onSubmitted: (_) => _onSearch(),
                  ),
                ),
                const SizedBox(width: 8),
                GestureDetector(
                  onTap: _onSearch,
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 16, vertical: 11),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [AppColors.primary, AppColors.primaryContainer],
                      ),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Text('Search',
                        style: TextStyle(
                            color: Colors.white, fontWeight: FontWeight.w700)),
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: inventoryAsync.when(
              data: (items) => items.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Container(
                            width: 64, height: 64,
                            decoration: BoxDecoration(
                              color: AppColors.surfaceContainerHigh,
                              borderRadius: BorderRadius.circular(16),
                            ),
                            child: const Icon(Icons.inventory_2_rounded,
                                size: 28, color: AppColors.onSurfaceVariant),
                          ),
                          const SizedBox(height: 12),
                          const Text('No inventory data found.',
                              style: TextStyle(
                                  color: AppColors.onSurfaceVariant)),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: () async =>
                          ref.invalidate(storeInventoryProvider(_filter)),
                      child: ListView.builder(
                        itemCount: items.length,
                        padding: const EdgeInsets.all(12),
                        itemBuilder: (context, index) {
                          final item = items[index];
                          final total = item.totalQuantity ??
                              (item.onShelfQuantity + item.warehouseQuantity);
                          final isLow = total < 10;

                          return Container(
                            margin: const EdgeInsets.only(bottom: 8),
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: AppColors.surfaceContainerLowest,
                              borderRadius: BorderRadius.circular(14),
                              border: Border(left: BorderSide(
                                color: isLow
                                    ? AppColors.error
                                    : AppColors.secondary,
                                width: 3,
                              )),
                            ),
                            child: Row(
                              children: [
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text(item.productName ?? 'Product',
                                          style: const TextStyle(
                                              fontWeight: FontWeight.w700,
                                              color: AppColors.onSurface)),
                                      if (item.productSku != null)
                                        Text('SKU: ${item.productSku}',
                                            style: const TextStyle(
                                                fontSize: 11,
                                                fontFamily: 'monospace',
                                                color: AppColors.outline)),
                                      if (item.storeName != null)
                                        Text(item.storeName!,
                                            style: const TextStyle(
                                                fontSize: 12,
                                                color:
                                                    AppColors.onSurfaceVariant)),
                                      const SizedBox(height: 4),
                                      Text(
                                          'Shelf: ${item.onShelfQuantity}  |  Warehouse: ${item.warehouseQuantity}',
                                          style: const TextStyle(
                                              fontSize: 12,
                                              color: AppColors.onSurfaceVariant)),
                                    ],
                                  ),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 12, vertical: 6),
                                  decoration: BoxDecoration(
                                    color: isLow
                                        ? AppColors.errorContainer
                                        : AppColors.secondaryContainer,
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: Text(
                                    '$total',
                                    style: TextStyle(
                                      fontWeight: FontWeight.w800,
                                      fontSize: 15,
                                      color: isLow
                                          ? AppColors.error
                                          : AppColors.secondary,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
                    ),
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
