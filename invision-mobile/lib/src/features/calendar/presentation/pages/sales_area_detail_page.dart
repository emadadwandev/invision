import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../providers/calendar_providers.dart';

class SalesAreaDetailPage extends ConsumerWidget {
  const SalesAreaDetailPage({super.key, required this.areaId});
  final int areaId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final areaAsync = ref.watch(salesAreaDetailProvider(areaId));

    return Scaffold(
      appBar: AppBar(title: const Text('Sales Area')),
      body: areaAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Error: $e')),
        data: (area) => SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(area.name,
                          style: Theme.of(context).textTheme.headlineSmall),
                      if (area.description != null) ...[
                        const SizedBox(height: 8),
                        Text(area.description!),
                      ],
                      const Divider(height: 24),
                      _InfoRow(label: 'Status',
                          value: area.isActive ? 'Active' : 'Inactive'),
                      if (area.managerName != null)
                        _InfoRow(label: 'Manager', value: area.managerName!),
                      _InfoRow(
                          label: 'Stores', value: '${area.storeCount}'),
                      _InfoRow(
                          label: 'Sub-areas',
                          value: '${area.children.length}'),
                    ],
                  ),
                ),
              ),
              if (area.children.isNotEmpty) ...[
                const SizedBox(height: 16),
                Text('Sub-areas',
                    style: Theme.of(context).textTheme.titleMedium),
                const SizedBox(height: 8),
                ...area.children.map((child) => Card(
                      child: ListTile(
                        title: Text(child.name),
                        subtitle: child.managerName != null
                            ? Text('Manager: ${child.managerName}')
                            : null,
                        trailing: Text('${child.storeCount} stores'),
                      ),
                    )),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow({required this.label, required this.value});
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
                style: const TextStyle(fontWeight: FontWeight.w600)),
          ),
          Expanded(child: Text(value)),
        ],
      ),
    );
  }
}
