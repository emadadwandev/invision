import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../providers/export_providers.dart';

class SavedExportsPage extends ConsumerWidget {
  const SavedExportsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final exportsAsync = ref.watch(savedExportsProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Export History')),
      body: exportsAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Error: $e')),
        data: (exports) {
          if (exports.isEmpty) {
            return const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.history, size: 64, color: Colors.grey),
                  SizedBox(height: 16),
                  Text('No exports yet'),
                ],
              ),
            );
          }
          return ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: exports.length,
            itemBuilder: (context, index) {
              final export = exports[index];
              return Card(
                margin: const EdgeInsets.only(bottom: 8),
                child: ListTile(
                  leading: CircleAvatar(
                    backgroundColor: _formatColor(export.format).withValues(alpha: 0.1),
                    child: Icon(_formatIcon(export.format), color: _formatColor(export.format)),
                  ),
                  title: Text(export.title),
                  subtitle: Text('${export.formatLabel} • ${export.formattedSize}\n${_formatDateTime(export.createdAt)}'),
                  isThreeLine: true,
                  trailing: IconButton(
                    icon: const Icon(Icons.delete_outline, color: Colors.red),
                    onPressed: () async {
                      final confirm = await showDialog<bool>(
                        context: context,
                        builder: (ctx) => AlertDialog(
                          title: const Text('Delete Export'),
                          content: const Text('Remove this export record?'),
                          actions: [
                            TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancel')),
                            TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Delete')),
                          ],
                        ),
                      );
                      if (confirm == true) {
                        await ref.read(exportRepositoryProvider).deleteSavedExport(export.id);
                        ref.invalidate(savedExportsProvider);
                      }
                    },
                  ),
                ),
              );
            },
          );
        },
      ),
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

  String _formatDateTime(DateTime date) {
    return '${date.day}/${date.month}/${date.year} ${date.hour}:${date.minute.toString().padLeft(2, '0')}';
  }
}
