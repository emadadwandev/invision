import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/enums/task_status.dart';
import '../../data/models/campaign_task_model.dart';
import '../providers/campaign_providers.dart';

class MyTasksPage extends ConsumerStatefulWidget {
  const MyTasksPage({super.key});

  @override
  ConsumerState<MyTasksPage> createState() => _MyTasksPageState();
}

class _MyTasksPageState extends ConsumerState<MyTasksPage> {
  String? _statusFilter;

  @override
  Widget build(BuildContext context) {
    final tasksAsync = ref.watch(myTasksProvider(_statusFilter));

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Tasks'),
        actions: [
          PopupMenuButton<String?>(
            icon: const Icon(Icons.filter_list),
            onSelected: (value) {
              setState(() => _statusFilter = value);
            },
            itemBuilder: (_) => [
              const PopupMenuItem(value: null, child: Text('All')),
              const PopupMenuItem(value: 'pending', child: Text('Pending')),
              const PopupMenuItem(
                  value: 'in_progress', child: Text('In Progress')),
              const PopupMenuItem(
                  value: 'completed', child: Text('Completed')),
            ],
          ),
        ],
      ),
      body: tasksAsync.when(
        data: (tasks) => tasks.isEmpty
            ? const Center(child: Text('No tasks found.'))
            : RefreshIndicator(
                onRefresh: () async =>
                    ref.invalidate(myTasksProvider(_statusFilter)),
                child: ListView.builder(
                  itemCount: tasks.length,
                  padding: const EdgeInsets.all(12),
                  itemBuilder: (context, index) =>
                      _TaskCard(task: tasks[index]),
                ),
              ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}

class _TaskCard extends StatelessWidget {
  const _TaskCard({required this.task});

  final CampaignTask task;

  Color _statusColor(TaskStatus status) {
    return switch (status) {
      TaskStatus.pending => Colors.grey,
      TaskStatus.inProgress => Colors.orange,
      TaskStatus.completed => Colors.blue,
      TaskStatus.verified => Colors.green,
      TaskStatus.rejected => Colors.red,
    };
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    task.campaignName ?? 'Task #${task.id}',
                    style: theme.textTheme.titleSmall,
                  ),
                ),
                Chip(
                  label: Text(
                    task.status.label,
                    style: TextStyle(
                      color: _statusColor(task.status),
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  backgroundColor:
                      _statusColor(task.status).withValues(alpha: 0.1),
                  side: BorderSide.none,
                  padding: EdgeInsets.zero,
                  visualDensity: VisualDensity.compact,
                ),
              ],
            ),
            if (task.storeName != null)
              Text(
                task.storeName!,
                style: theme.textTheme.bodySmall?.copyWith(
                  color: theme.colorScheme.primary,
                ),
              ),
            if (task.instructions != null) ...[
              const SizedBox(height: 4),
              Text(
                task.instructions!,
                style: theme.textTheme.bodySmall,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ],
          ],
        ),
      ),
    );
  }
}
