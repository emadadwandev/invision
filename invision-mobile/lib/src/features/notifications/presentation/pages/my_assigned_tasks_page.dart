import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/enums/task_assignment_status.dart';
import '../../data/models/task_assignment_model.dart';
import '../providers/notifications_providers.dart';

class MyAssignedTasksPage extends ConsumerStatefulWidget {
  const MyAssignedTasksPage({super.key});

  @override
  ConsumerState<MyAssignedTasksPage> createState() =>
      _MyAssignedTasksPageState();
}

class _MyAssignedTasksPageState extends ConsumerState<MyAssignedTasksPage> {
  String? _statusFilter;

  @override
  Widget build(BuildContext context) {
    final tasksAsync = ref.watch(myAssignedTasksProvider(_statusFilter));

    return Scaffold(
      appBar: AppBar(title: const Text('My Assigned Tasks')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _FilterChip(
                    label: 'All',
                    selected: _statusFilter == null,
                    onSelected: () => setState(() => _statusFilter = null),
                  ),
                  const SizedBox(width: 8),
                  ...TaskAssignmentStatus.values.map((s) => Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: _FilterChip(
                          label: s.label,
                          selected: _statusFilter == s.value,
                          onSelected: () =>
                              setState(() => _statusFilter = s.value),
                        ),
                      )),
                ],
              ),
            ),
          ),
          Expanded(
            child: tasksAsync.when(
              data: (tasks) => tasks.isEmpty
                  ? const Center(child: Text('No tasks assigned.'))
                  : RefreshIndicator(
                      onRefresh: () async =>
                          ref.invalidate(myAssignedTasksProvider(_statusFilter)),
                      child: ListView.builder(
                        itemCount: tasks.length,
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        itemBuilder: (context, index) =>
                            _TaskCard(task: tasks[index]),
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

class _FilterChip extends StatelessWidget {
  const _FilterChip({
    required this.label,
    required this.selected,
    required this.onSelected,
  });

  final String label;
  final bool selected;
  final VoidCallback onSelected;

  @override
  Widget build(BuildContext context) {
    return FilterChip(
      label: Text(label),
      selected: selected,
      onSelected: (_) => onSelected(),
    );
  }
}

class _TaskCard extends StatelessWidget {
  const _TaskCard({required this.task});

  final TaskAssignment task;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Card(
      color: (task.isOverdue ?? false)
          ? Colors.red.withAlpha(20)
          : null,
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        onTap: () => context.push('/assigned-tasks/${task.id}'),
        title: Text(task.title, style: theme.textTheme.titleSmall),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (task.assignerName != null)
              Text('Assigned by: ${task.assignerName!}',
                  style: theme.textTheme.bodySmall),
            const SizedBox(height: 4),
            Row(
              children: [
                Chip(
                  label: Text(task.status.label,
                      style: TextStyle(fontSize: 10, color: task.status.color)),
                  padding: EdgeInsets.zero,
                  materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  visualDensity: VisualDensity.compact,
                  side: BorderSide(color: task.status.color),
                ),
                const SizedBox(width: 4),
                Chip(
                  label: Text(task.priority.label,
                      style: TextStyle(fontSize: 10, color: task.priority.color)),
                  padding: EdgeInsets.zero,
                  materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  visualDensity: VisualDensity.compact,
                  side: BorderSide(color: task.priority.color),
                ),
              ],
            ),
            if (task.dueDate != null) ...[
              const SizedBox(height: 4),
              Row(
                children: [
                  Icon(Icons.calendar_today, size: 14,
                      color: (task.isOverdue ?? false) ? Colors.red : Colors.grey),
                  const SizedBox(width: 4),
                  Text(
                    'Due: ${task.dueDate}',
                    style: theme.textTheme.bodySmall?.copyWith(
                      color: (task.isOverdue ?? false) ? Colors.red : null,
                      fontWeight: (task.isOverdue ?? false) ? FontWeight.bold : null,
                    ),
                  ),
                  if (task.isOverdue ?? false) ...[
                    const SizedBox(width: 4),
                    const Text('OVERDUE',
                        style: TextStyle(fontSize: 10, color: Colors.red, fontWeight: FontWeight.bold)),
                  ],
                ],
              ),
            ],
          ],
        ),
        isThreeLine: true,
      ),
    );
  }
}
