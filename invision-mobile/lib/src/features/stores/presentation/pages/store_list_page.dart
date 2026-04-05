import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

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

    return Scaffold(
      appBar: AppBar(title: const Text('Stores')),
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
                      hintText: 'Search stores...',
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
            child: storesAsync.when(
              data: (stores) => stores.isEmpty
                  ? const Center(child: Text('No stores found.'))
                  : RefreshIndicator(
                      onRefresh: () async =>
                          ref.invalidate(storesProvider(_filter)),
                      child: ListView.builder(
                        itemCount: stores.length,
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        itemBuilder: (context, index) =>
                            _StoreCard(store: stores[index]),
                      ),
                    ),
              loading: () => const Center(child: CircularProgressIndicator()),
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
    final theme = Theme.of(context);

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        title: Text(store.name, style: theme.textTheme.titleSmall),
        subtitle: Text(
          '${store.code} · ${store.category.label} · ${store.rank.label}',
          style: theme.textTheme.bodySmall,
        ),
        trailing: Icon(
          Icons.circle,
          size: 12,
          color: store.isActive ? Colors.green : Colors.red,
        ),
        onTap: () => context.push('/stores/${store.id}'),
      ),
    );
  }
}
