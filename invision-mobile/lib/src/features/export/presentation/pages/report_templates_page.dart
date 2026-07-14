import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/theme/app_theme.dart';
import '../../data/models/export_models.dart';
import '../providers/export_providers.dart';

class ReportTemplatesPage extends ConsumerWidget {
  const ReportTemplatesPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final templatesAsync = ref.watch(reportTemplatesProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Report Templates',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _showCreateDialog(context, ref),
        backgroundColor: AppColors.primary,
        child: const Icon(Icons.add, color: Colors.white),
      ),
      body: templatesAsync.when(
        loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
        error: (e, _) => Center(child: Text('Error: $e')),
        data: (templates) {
          if (templates.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Container(
                    width: 64, height: 64,
                    decoration: BoxDecoration(
                        color: AppColors.surfaceContainerHigh,
                        borderRadius: BorderRadius.circular(16)),
                    child: const Icon(Icons.description_outlined, size: 32, color: AppColors.outline),
                  ),
                  const SizedBox(height: 16),
                  const Text('No report templates yet',
                      style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.onSurface)),
                  const SizedBox(height: 6),
                  const Text('Create your first custom report template',
                      style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 13)),
                ],
              ),
            );
          }
          return ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: templates.length,
            itemBuilder: (context, index) => _TemplateCard(template: templates[index], ref: ref),
          );
        },
      ),
    );
  }

  void _showCreateDialog(BuildContext context, WidgetRef ref) {
    final nameController = TextEditingController();
    String selectedType = 'custom';

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setState) => AlertDialog(
          title: const Text('New Report Template'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: nameController,
                decoration: const InputDecoration(
                  labelText: 'Template Name',
                  hintText: 'e.g., Monthly Sales Summary',
                ),
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                initialValue: selectedType,
                decoration: const InputDecoration(labelText: 'Report Type'),
                items: const [
                  DropdownMenuItem(value: 'overview', child: Text('Overview')),
                  DropdownMenuItem(value: 'sales', child: Text('Sales')),
                  DropdownMenuItem(value: 'field_activity', child: Text('Field Activity')),
                  DropdownMenuItem(value: 'custom', child: Text('Custom')),
                ],
                onChanged: (v) => setState(() => selectedType = v ?? 'custom'),
              ),
            ],
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancel')),
            FilledButton(
              onPressed: () async {
                if (nameController.text.isEmpty) return;
                await ref.read(exportRepositoryProvider).createReportTemplate({
                  'name': nameController.text,
                  'type': selectedType,
                  'config': {'entity': 'sales_orders'},
                });
                if (ctx.mounted) Navigator.pop(ctx);
                ref.invalidate(reportTemplatesProvider);
              },
              child: const Text('Create'),
            ),
          ],
        ),
      ),
    );
  }
}

class _TemplateCard extends StatelessWidget {
  final ReportTemplateModel template;
  final WidgetRef ref;

  const _TemplateCard({required this.template, required this.ref});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(color: AppColors.primary.withOpacity(0.05),
              blurRadius: 6, offset: const Offset(0, 2)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 36, height: 36,
                decoration: BoxDecoration(
                  color: _typeColor(template.type).withOpacity(0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(_typeIcon(template.type), color: _typeColor(template.type), size: 18),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  template.name,
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold, color: AppColors.onSurface),
                ),
              ),
              if (template.isFavorite)
                const Icon(Icons.star_rounded, color: Color(0xFFFFA000), size: 20),
              if (template.isShared)
                const Icon(Icons.people_rounded, color: AppColors.primaryContainer, size: 20),
            ],
          ),
          if (template.description != null) ...[
            const SizedBox(height: 8),
            Text(template.description!,
                style: Theme.of(context).textTheme.bodySmall
                    ?.copyWith(color: AppColors.onSurfaceVariant)),
          ],
          const SizedBox(height: 10),
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: _typeColor(template.type).withOpacity(0.1),
                  borderRadius: BorderRadius.circular(100),
                ),
                child: Text(
                  template.type.replaceAll('_', ' ').toUpperCase(),
                  style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700,
                      color: _typeColor(template.type)),
                ),
              ),
              const Spacer(),
              if (template.lastGeneratedAt != null)
                Text(
                  'Last: ${_formatDate(template.lastGeneratedAt!)}',
                  style: Theme.of(context).textTheme.bodySmall
                      ?.copyWith(color: AppColors.onSurfaceVariant),
                ),
            ],
          ),
          const Padding(
              padding: EdgeInsets.symmetric(vertical: 12),
              child: Divider(color: AppColors.outlineVariant, height: 1)),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceEvenly,
            children: [
              _ActionButton(icon: Icons.play_arrow_rounded, label: 'Generate',
                  onTap: () => _generate(context)),
              _ActionButton(icon: Icons.grid_on_rounded, label: 'Excel',
                  onTap: () => _exportExcel(context)),
              _ActionButton(icon: Icons.picture_as_pdf_rounded, label: 'PDF',
                  onTap: () => _exportPdf(context)),
              _ActionButton(icon: Icons.table_chart_rounded, label: 'CSV',
                  onTap: () => _exportCsv(context)),
            ],
          ),
        ],
      ),
    );
  }

  void _generate(BuildContext context) async {
    try {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Generating report...')));
      await ref.read(exportRepositoryProvider).generateFromTemplate(template.id);
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Report generated!')));
      }
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
      }
    }
  }

  void _exportExcel(BuildContext context) async {
    try {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Exporting Excel...')));
      await ref.read(exportRepositoryProvider).exportTemplateExcel(template.id);
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Excel exported!')));
      }
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
      }
    }
  }

  void _exportPdf(BuildContext context) async {
    try {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Exporting PDF...')));
      await ref.read(exportRepositoryProvider).exportTemplatePdf(template.id);
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('PDF exported!')));
      }
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
      }
    }
  }

  void _exportCsv(BuildContext context) async {
    try {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Exporting CSV...')));
      await ref.read(exportRepositoryProvider).exportTemplateCsv(template.id);
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('CSV exported!')));
        ref.invalidate(savedExportsProvider);
      }
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
      }
    }
  }

  IconData _typeIcon(String type) {
    return switch (type) {
      'overview' => Icons.dashboard,
      'sales' => Icons.trending_up,
      'field_activity' => Icons.directions_walk,
      _ => Icons.description,
    };
  }

  Color _typeColor(String type) {
    return switch (type) {
      'overview' => AppColors.primaryContainer,
      'sales' => AppColors.secondary,
      'field_activity' => AppColors.tertiary,
      _ => AppColors.primary,
    };
  }

  String _formatDate(DateTime date) {
    return '${date.day}/${date.month}/${date.year}';
  }
}

class _ActionButton extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  const _ActionButton({required this.icon, required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        child: Column(
          children: [
            Icon(icon, size: 20),
            const SizedBox(height: 4),
            Text(label, style: Theme.of(context).textTheme.labelSmall),
          ],
        ),
      ),
    );
  }
}
