import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../data/models/calendar_models.dart';
import '../providers/calendar_providers.dart';

class SalesAreaListPage extends ConsumerWidget {
  const SalesAreaListPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final areasAsync = ref.watch(salesAreasHierarchyProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Sales Areas')),
      body: areasAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Error: $e')),
        data: (areas) => areas.isEmpty
            ? const Center(child: Text('No sales areas defined'))
            : ListView.builder(
                padding: const EdgeInsets.all(8),
                itemCount: areas.length,
                itemBuilder: (context, i) =>
                    _SalesAreaCard(area: areas[i], depth: 0),
              ),
      ),
    );
  }
}

class _SalesAreaCard extends StatelessWidget {
  const _SalesAreaCard({required this.area, required this.depth});
  final SalesAreaModel area;
  final int depth;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(left: depth * 16.0),
      child: Column(
        children: [
          Card(
            margin: const EdgeInsets.symmetric(vertical: 4),
            child: ListTile(
              leading: CircleAvatar(
                backgroundColor: area.isActive
                    ? Colors.blue.shade50
                    : Colors.grey.shade200,
                child: Icon(
                  depth == 0 ? Icons.map : Icons.subdirectory_arrow_right,
                  color:
                      area.isActive ? Colors.blue.shade700 : Colors.grey,
                  size: 20,
                ),
              ),
              title: Text(
                area.name,
                style: TextStyle(
                  fontWeight: depth == 0 ? FontWeight.bold : FontWeight.w500,
                ),
              ),
              subtitle: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (area.managerName != null)
                    Text('Manager: ${area.managerName}'),
                  if (area.storeCount > 0)
                    Text('${area.storeCount} store(s)'),
                ],
              ),
              trailing: area.isActive
                  ? const Icon(Icons.check_circle,
                      color: Colors.green, size: 18)
                  : const Icon(Icons.cancel, color: Colors.red, size: 18),
              onTap: () => context.push('/sales-areas/${area.id}'),
            ),
          ),
          // Render children recursively
          ...area.children.map(
            (child) => _SalesAreaCard(area: child, depth: depth + 1),
          ),
        ],
      ),
    );
  }
}
