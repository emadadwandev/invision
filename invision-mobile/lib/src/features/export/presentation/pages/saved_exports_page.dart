import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/theme/app_theme.dart';
import '../providers/export_providers.dart';

class SavedExportsPage extends ConsumerWidget {
  const SavedExportsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final exportsAsync = ref.watch(savedExportsProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Export History',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: exportsAsync.when(
        loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
        error: (e, _) => Center(child: Text('Error: $e')),
        data: (exports) {
          if (exports.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Container(
                    width: 64, height: 64,
                    decoration: BoxDecoration(
                        color: AppColors.surfaceContainerHigh,
                        borderRadius: BorderRadius.circular(16)),
                    child: const Icon(Icons.history_rounded, size: 32, color: AppColors.outline),
                  ),
                  const SizedBox(height: 14),
                  const Text('No exports yet',
                      style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.onSurface)),
                ],
              ),
            );
          }
          return ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: exports.length,
            itemBuilder: (context, index) {
              final export = exports[index];
              return Container(
                margin: const EdgeInsets.only(bottom: 8),
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: AppColors.surfaceContainerLowest,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
                ),
                child: Row(
                  children: [
                    Container(
                      width: 44, height: 44,
                      decoration: BoxDecoration(
                        color: _formatColor(export.format).withOpacity(0.1),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Icon(_formatIcon(export.format),
                          color: _formatColor(export.format), size: 22),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(export.title,
                              style: const TextStyle(
                                  fontWeight: FontWeight.w700, color: AppColors.onSurface, fontSize: 13)),
                          const SizedBox(height: 2),
                          Text('${export.formatLabel} · ${export.formattedSize}',
                              style: const TextStyle(fontSize: 12, color: AppColors.onSurfaceVariant)),
                          Text(_formatDateTime(export.createdAt),
                              style: const TextStyle(fontSize: 11, color: AppColors.outline)),
                        ],
                      ),
                    ),
                    GestureDetector(
                      onTap: () async {
                        final confirm = await showDialog<bool>(
                          context: context,
                          builder: (ctx) => AlertDialog(
                            title: const Text('Delete Export'),
                            content: const Text('Remove this export record?'),
                            actions: [
                              TextButton(onPressed: () => Navigator.pop(ctx, false),
                                  child: const Text('Cancel')),
                              TextButton(onPressed: () => Navigator.pop(ctx, true),
                                  child: const Text('Delete')),
                            ],
                          ),
                        );
                        if (confirm == true) {
                          await ref.read(exportRepositoryProvider).deleteSavedExport(export.id);
                          ref.invalidate(savedExportsProvider);
                        }
                      },
                      child: Container(
                        width: 36, height: 36,
                        decoration: BoxDecoration(
                          color: AppColors.errorContainer,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Icon(Icons.delete_outline_rounded,
                            color: AppColors.error, size: 18),
                      ),
                    ),
                  ],
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
      'csv' => AppColors.secondary,
      'excel' => AppColors.secondary,
      'pdf' => AppColors.error,
      'presentation' => AppColors.primaryContainer,
      'html' => AppColors.tertiary,
      _ => AppColors.outline,
    };
  }

  String _formatDateTime(DateTime date) {
    return '${date.day}/${date.month}/${date.year} ${date.hour}:${date.minute.toString().padLeft(2, '0')}';
  }
}
