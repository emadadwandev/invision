import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../data/models/export_models.dart';
import '../providers/export_providers.dart';

class ReportTemplatesPage extends ConsumerWidget {
  const ReportTemplatesPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final templatesAsync = ref.watch(reportTemplatesProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Report Templates')),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _showCreateDialog(context, ref),
        child: const Icon(Icons.add),
      ),
      body: templatesAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Error: $e')),
        data: (templates) {
          if (templates.isEmpty) {
            return const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.description_outlined, size: 64, color: Colors.grey),
                  SizedBox(height: 16),
                  Text('No report templates yet'),
                  SizedBox(height: 8),
                  Text('Create your first custom report template', style: TextStyle(color: Colors.grey)),
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
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(_typeIcon(template.type), color: _typeColor(template.type)),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    template.name,
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
                  ),
                ),
                if (template.isFavorite) const Icon(Icons.star, color: Colors.amber, size: 20),
                if (template.isShared) const Icon(Icons.people, color: Colors.blue, size: 20),
              ],
            ),
            if (template.description != null) ...[
              const SizedBox(height: 8),
              Text(template.description!, style: Theme.of(context).textTheme.bodySmall),
            ],
            const SizedBox(height: 8),
            Row(
              children: [
                Chip(
                  label: Text(template.type.replaceAll('_', ' ').toUpperCase()),
                  materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  visualDensity: VisualDensity.compact,
                ),
                const Spacer(),
                if (template.lastGeneratedAt != null)
                  Text(
                    'Last: ${_formatDate(template.lastGeneratedAt!)}',
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey),
                  ),
              ],
            ),
            const Divider(height: 20),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                _ActionButton(
                  icon: Icons.play_arrow,
                  label: 'Generate',
                  onTap: () => _generate(context),
                ),
                _ActionButton(
                  icon: Icons.grid_on,
                  label: 'Excel',
                  onTap: () => _exportExcel(context),
                ),
                _ActionButton(
                  icon: Icons.picture_as_pdf,
                  label: 'PDF',
                  onTap: () => _exportPdf(context),
                ),
                _ActionButton(
                  icon: Icons.table_chart,
                  label: 'CSV',
                  onTap: () => _exportCsv(context),
                ),
              ],
            ),
          ],
        ),
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
      'overview' => Colors.blue,
      'sales' => Colors.green,
      'field_activity' => Colors.orange,
      _ => Colors.purple,
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
