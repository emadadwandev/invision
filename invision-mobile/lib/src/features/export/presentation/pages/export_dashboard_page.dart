import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../providers/export_providers.dart';

class ExportDashboardPage extends ConsumerWidget {
  const ExportDashboardPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      appBar: AppBar(title: const Text('Export & Presentations')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Quick Actions
            Text('Quick Actions', style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: _QuickActionCard(
                    icon: Icons.slideshow,
                    label: 'Market Review',
                    color: Colors.blue,
                    onTap: () => context.push('/presentations/market-review'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _QuickActionCard(
                    icon: Icons.description,
                    label: 'Report Templates',
                    color: Colors.green,
                    onTap: () => context.push('/report-templates'),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: _QuickActionCard(
                    icon: Icons.view_carousel,
                    label: 'Presentation Templates',
                    color: Colors.orange,
                    onTap: () => context.push('/presentation-templates'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _QuickActionCard(
                    icon: Icons.history,
                    label: 'Export History',
                    color: Colors.purple,
                    onTap: () => context.push('/saved-exports'),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 24),

            // Recent Exports
            Text('Recent Exports', style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 12),
            _RecentExportsList(ref: ref),
          ],
        ),
      ),
    );
  }
}

class _QuickActionCard extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  const _QuickActionCard({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, size: 32, color: color),
              ),
              const SizedBox(height: 12),
              Text(
                label,
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.titleSmall,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _RecentExportsList extends StatelessWidget {
  final WidgetRef ref;

  const _RecentExportsList({required this.ref});

  @override
  Widget build(BuildContext context) {
    final exportsAsync = ref.watch(savedExportsProvider);

    return exportsAsync.when(
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (e, _) => Card(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Text('Could not load exports: $e'),
        ),
      ),
      data: (exports) {
        if (exports.isEmpty) {
          return const Card(
            child: Padding(
              padding: EdgeInsets.all(24),
              child: Center(child: Text('No exports yet. Generate your first report!')),
            ),
          );
        }
        return Column(
          children: exports.take(5).map((export) {
            return Card(
              child: ListTile(
                leading: Icon(_formatIcon(export.format), color: _formatColor(export.format)),
                title: Text(export.title),
                subtitle: Text('${export.formatLabel} • ${export.formattedSize}'),
                trailing: Text(
                  _formatDate(export.createdAt),
                  style: Theme.of(context).textTheme.bodySmall,
                ),
              ),
            );
          }).toList(),
        );
      },
    );
  }

  IconData _formatIcon(String format) {
    return switch (format) {
      'csv' => Icons.table_chart,
      'excel' => Icons.grid_on,
      'pdf' => Icons.picture_as_pdf,
      'presentation' => Icons.slideshow,
      'html' => Icons.web,
      _ => Icons.file_present,
    };
  }

  Color _formatColor(String format) {
    return switch (format) {
      'csv' => Colors.teal,
      'excel' => Colors.green,
      'pdf' => Colors.red,
      'presentation' => Colors.blue,
      'html' => Colors.orange,
      _ => Colors.grey,
    };
  }

  String _formatDate(DateTime date) {
    final now = DateTime.now();
    final diff = now.difference(date);
    if (diff.inMinutes < 60) return '${diff.inMinutes}m ago';
    if (diff.inHours < 24) return '${diff.inHours}h ago';
    return '${diff.inDays}d ago';
  }
}
