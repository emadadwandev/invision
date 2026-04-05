import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/enums/task_assignment_status.dart';
import '../providers/notifications_providers.dart';

class TaskAssignmentDetailPage extends ConsumerStatefulWidget {
  const TaskAssignmentDetailPage({super.key, required this.taskId});

  final int taskId;

  @override
  ConsumerState<TaskAssignmentDetailPage> createState() =>
      _TaskAssignmentDetailPageState();
}

class _TaskAssignmentDetailPageState
    extends ConsumerState<TaskAssignmentDetailPage> {
  final _notesController = TextEditingController();
  bool _isCompleting = false;

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _completeTask() async {
    setState(() => _isCompleting = true);
    try {
      final repo = ref.read(notificationsRepositoryProvider);
      await repo.completeTask(
        id: widget.taskId,
        completionNotes: _notesController.text.isNotEmpty
            ? _notesController.text
            : null,
      );
      ref.invalidate(taskAssignmentDetailProvider(widget.taskId));
      ref.invalidate(myAssignedTasksProvider(null));
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Task marked as completed!')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _isCompleting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final taskAsync = ref.watch(taskAssignmentDetailProvider(widget.taskId));
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('Task Details')),
      body: taskAsync.when(
        data: (task) => SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(task.title, style: theme.textTheme.headlineSmall),
              const SizedBox(height: 8),
              Row(
                children: [
                  Chip(
                    label: Text(task.status.label,
                        style: TextStyle(color: task.status.color)),
                    side: BorderSide(color: task.status.color),
                  ),
                  const SizedBox(width: 8),
                  Chip(
                    label: Text(task.priority.label,
                        style: TextStyle(color: task.priority.color)),
                    side: BorderSide(color: task.priority.color),
                  ),
                ],
              ),
              const Divider(height: 24),
              _InfoRow('Assigned by', task.assignerName ?? 'Unknown'),
              _InfoRow('Assigned to', task.assigneeName ?? 'Unknown'),
              if (task.dueDate != null) ...[
                _InfoRow('Due date', task.dueDate!),
                if (task.isOverdue ?? false)
                  const Padding(
                    padding: EdgeInsets.only(left: 8, bottom: 8),
                    child: Text('OVERDUE',
                        style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold)),
                  ),
              ],
              if (task.createdAt != null)
                _InfoRow('Created', task.createdAt!.substring(0, 10)),
              if (task.completedAt != null)
                _InfoRow('Completed', task.completedAt!.substring(0, 10)),
              const Divider(height: 24),
              if (task.description != null && task.description!.isNotEmpty) ...[
                Text('Description', style: theme.textTheme.titleSmall),
                const SizedBox(height: 8),
                Text(task.description!, style: theme.textTheme.bodyLarge),
                const Divider(height: 24),
              ],
              if (task.completionNotes != null &&
                  task.completionNotes!.isNotEmpty) ...[
                Text('Completion Notes', style: theme.textTheme.titleSmall),
                const SizedBox(height: 8),
                Text(task.completionNotes!, style: theme.textTheme.bodyLarge),
                const Divider(height: 24),
              ],
              // Complete task action
              if (task.status == TaskAssignmentStatus.pending ||
                  task.status == TaskAssignmentStatus.inProgress) ...[
                Text('Complete Task', style: theme.textTheme.titleSmall),
                const SizedBox(height: 8),
                TextField(
                  controller: _notesController,
                  maxLines: 3,
                  decoration: const InputDecoration(
                    hintText: 'Completion notes (optional)...',
                    border: OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 12),
                SizedBox(
                  width: double.infinity,
                  child: FilledButton.icon(
                    onPressed: _isCompleting ? null : _completeTask,
                    icon: _isCompleting
                        ? const SizedBox(
                            width: 16,
                            height: 16,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : const Icon(Icons.check),
                    label: const Text('Mark as Completed'),
                  ),
                ),
              ],
            ],
          ),
        ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow(this.label, this.value);

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(label,
                style: Theme.of(context)
                    .textTheme
                    .bodySmall
                    ?.copyWith(color: Colors.grey)),
          ),
          Expanded(child: Text(value)),
        ],
      ),
    );
  }
}
