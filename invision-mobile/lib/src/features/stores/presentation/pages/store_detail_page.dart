import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../data/models/store_model.dart';
import '../../presentation/providers/store_providers.dart';

class StoreDetailPage extends ConsumerWidget {
  const StoreDetailPage({required this.storeId, super.key});

  final int storeId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final storeAsync = ref.watch(storeDetailProvider(storeId));

    return Scaffold(
      appBar: AppBar(title: const Text('Store Details')),
      body: storeAsync.when(
        data: (store) => _StoreDetailBody(store: store),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}

class _StoreDetailBody extends StatelessWidget {
  const _StoreDetailBody({required this.store});

  final Store store;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Header
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(store.name,
                          style: theme.textTheme.headlineSmall),
                    ),
                    Chip(
                      label: Text(store.isActive ? 'Active' : 'Inactive'),
                      backgroundColor:
                          store.isActive ? Colors.green[50] : Colors.red[50],
                      labelStyle: TextStyle(
                        color: store.isActive ? Colors.green : Colors.red,
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Text('Code: ${store.code}',
                    style: theme.textTheme.bodyMedium),
              ],
            ),
          ),
        ),
        const SizedBox(height: 12),

        // Info
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Information', style: theme.textTheme.titleMedium),
                const Divider(),
                _InfoRow('Category', store.category.label),
                _InfoRow('Rank', store.rank.label),
                if (store.areaName != null)
                  _InfoRow('Area', store.areaName!),
                if (store.address != null)
                  _InfoRow('Address', store.address!),
                if (store.gpsLatitude != null && store.gpsLongitude != null)
                  _InfoRow(
                    'GPS',
                    '${store.gpsLatitude!.toStringAsFixed(6)}, ${store.gpsLongitude!.toStringAsFixed(6)}',
                  ),
              ],
            ),
          ),
        ),

        // Contacts
        if (store.contacts.isNotEmpty) ...[
          const SizedBox(height: 12),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Contacts', style: theme.textTheme.titleMedium),
                  const Divider(),
                  ...store.contacts.map(
                    (c) => ListTile(
                      dense: true,
                      contentPadding: EdgeInsets.zero,
                      leading: Icon(
                        c.isPrimary ? Icons.star : Icons.person,
                        color: c.isPrimary ? Colors.amber : null,
                        size: 20,
                      ),
                      title: Text(c.name),
                      subtitle: Text([
                        if (c.position != null) c.position!,
                        if (c.phone != null) c.phone!,
                      ].join(' · ')),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ],
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow(this.label, this.value);

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          SizedBox(
            width: 100,
            child: Text(label,
                style: Theme.of(context)
                    .textTheme
                    .bodySmall
                    ?.copyWith(color: Colors.grey[600])),
          ),
          Expanded(child: Text(value)),
        ],
      ),
    );
  }
}
