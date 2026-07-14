import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/theme/app_theme.dart';
import '../../data/models/store_model.dart';
import '../../presentation/providers/store_providers.dart';

class StoreListPage extends ConsumerStatefulWidget {
  const StoreListPage({super.key});

  @override
  ConsumerState<StoreListPage> createState() => _StoreListPageState();
}

class _StoreListPageState extends ConsumerState<StoreListPage> {
  final _searchController = TextEditingController();
  StoreFilter _filter = const StoreFilter();

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
    final storesAsync = ref.watch(storesProvider(_filter));
    final tt = Theme.of(context).textTheme;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Stores', style: tt.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0,
        scrolledUnderElevation: 0,
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
                    style: tt.bodyMedium?.copyWith(color: AppColors.onSurface),
                    decoration: const InputDecoration(
                      hintText: 'Search stores...',
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
            child: storesAsync.when(
              data: (stores) => stores.isEmpty
                  ? Center(
                      child: Column(mainAxisSize: MainAxisSize.min, children: [
                        Container(
                          width: 64, height: 64,
                          decoration: BoxDecoration(
                            color: AppColors.surfaceContainerHigh,
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: const Icon(Icons.store_rounded, size: 32, color: AppColors.outline),
                        ),
                        const SizedBox(height: 12),
                        Text('No stores found.',
                            style: tt.bodyLarge?.copyWith(color: AppColors.onSurfaceVariant)),
                      ]),
                    )
                  : RefreshIndicator(
                      color: AppColors.primary,
                      onRefresh: () async => ref.invalidate(storesProvider(_filter)),
                      child: ListView.builder(
                        itemCount: stores.length,
                        padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
                        itemBuilder: (context, index) => _StoreCard(store: stores[index]),
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

class _StoreCard extends StatelessWidget {
  const _StoreCard({required this.store});
  final Store store;

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;
    return GestureDetector(
      onTap: () => context.push('/stores/${store.id}'),
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surfaceContainerLowest,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
        ),
        child: Row(
          children: [
            Container(
              width: 44, height: 44,
              decoration: BoxDecoration(
                color: AppColors.surfaceContainerLow,
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(Icons.store_rounded, color: AppColors.primary, size: 22),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(store.name,
                      style: tt.titleSmall?.copyWith(
                          color: AppColors.onSurface, fontWeight: FontWeight.w700)),
                  const SizedBox(height: 3),
                  Text(
                    '${store.code}  ·  ${store.category.label}  ·  ${store.rank.label}',
                    style: tt.bodySmall?.copyWith(color: AppColors.onSurfaceVariant),
                  ),
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: store.isActive ? AppColors.secondaryContainer : AppColors.errorContainer,
                borderRadius: BorderRadius.circular(100),
              ),
              child: Text(
                store.isActive ? 'Active' : 'Inactive',
                style: TextStyle(
                    fontSize: 10, fontWeight: FontWeight.w700,
                    color: store.isActive ? AppColors.onSecondaryContainer : AppColors.onErrorContainer),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
