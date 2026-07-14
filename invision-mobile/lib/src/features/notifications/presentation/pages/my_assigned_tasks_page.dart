import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/enums/task_assignment_status.dart';
import '../../../../core/theme/app_theme.dart';
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
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('My Assigned Tasks',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(12, 12, 12, 0),
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
                          onSelected: () => setState(() => _statusFilter = s.value),
                        ),
                      )),
                ],
              ),
            ),
          ),
          const SizedBox(height: 8),
          Expanded(
            child: tasksAsync.when(
              data: (tasks) => tasks.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Container(
                            width: 64, height: 64,
                            decoration: BoxDecoration(
                                color: AppColors.surfaceContainerHigh,
                                borderRadius: BorderRadius.circular(16)),
                            child: const Icon(Icons.task_alt_rounded, size: 32, color: AppColors.outline),
                          ),
                          const SizedBox(height: 12),
                          Text('No tasks assigned.',
                              style: Theme.of(context).textTheme.bodyLarge
                                  ?.copyWith(color: AppColors.onSurfaceVariant)),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: () async =>
                          ref.invalidate(myAssignedTasksProvider(_statusFilter)),
                      child: ListView.builder(
                        itemCount: tasks.length,
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                        itemBuilder: (context, index) =>
                            _TaskCard(task: tasks[index]),
                      ),
                    ),
              loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
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
    return GestureDetector(
      onTap: () => selected ? null : onSelected(),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: selected ? AppColors.primary : AppColors.surfaceContainerLow,
          borderRadius: BorderRadius.circular(100),
        ),
        child: Text(label,
            style: TextStyle(
                color: selected ? Colors.white : AppColors.onSurfaceVariant,
                fontWeight: selected ? FontWeight.w700 : FontWeight.w500,
                fontSize: 12)),
      ),
    );
  }
}

class _TaskCard extends StatelessWidget {
  const _TaskCard({required this.task});

  final TaskAssignment task;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return GestureDetector(
      onTap: () => context.push('/assigned-tasks/${task.id}'),
      child: Container(
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: (task.isOverdue ?? false)
              ? AppColors.errorContainer.withOpacity(0.4)
              : AppColors.surfaceContainerLowest,
          borderRadius: BorderRadius.circular(14),
          border: Border(
            left: BorderSide(
              color: (task.isOverdue ?? false) ? AppColors.error : task.status.color,
              width: 3,
            ),
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(task.title,
                style: Theme.of(context).textTheme.titleSmall
                    ?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w700)),
            if (task.assignerName != null) ...[const SizedBox(height: 4),
              Text('Assigned by: ${task.assignerName!}',
                  style: Theme.of(context).textTheme.bodySmall
                      ?.copyWith(color: AppColors.onSurfaceVariant))],
            const SizedBox(height: 8),
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                  decoration: BoxDecoration(
                    color: AppColors.surfaceContainerHigh,
                    borderRadius: BorderRadius.circular(100),
                  ),
                  child: Text(task.status.label,
                      style: TextStyle(fontSize: 9, color: task.status.color, fontWeight: FontWeight.w700)),
                ),
                const SizedBox(width: 6),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                  decoration: BoxDecoration(
                    color: AppColors.surfaceContainerHigh,
                    borderRadius: BorderRadius.circular(100),
                  ),
                  child: Text(task.priority.label,
                      style: TextStyle(fontSize: 9, color: task.priority.color, fontWeight: FontWeight.w700)),
                ),
                if (task.dueDate != null) ...[const SizedBox(width: 8),
                  Icon(Icons.calendar_today_rounded, size: 12,
                      color: (task.isOverdue ?? false) ? AppColors.error : AppColors.onSurfaceVariant),
                  const SizedBox(width: 3),
                  Text(
                    'Due: ${task.dueDate}',
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: (task.isOverdue ?? false) ? AppColors.error : AppColors.onSurfaceVariant,
                      fontWeight: (task.isOverdue ?? false) ? FontWeight.w700 : null,
                    ),
                  ),
                  if (task.isOverdue ?? false) ...[const SizedBox(width: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                          color: AppColors.errorContainer,
                          borderRadius: BorderRadius.circular(100)),
                      child: const Text('OVERDUE',
                          style: TextStyle(fontSize: 8, color: AppColors.error, fontWeight: FontWeight.w700)),
                    )]],
              ],
            ),
          ],
        ),
      ),
    );
  }
}
