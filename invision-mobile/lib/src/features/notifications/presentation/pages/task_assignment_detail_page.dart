import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/enums/task_assignment_status.dart';
import '../../../../core/theme/app_theme.dart';
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
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Task Details',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: taskAsync.when(
        data: (task) => SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [task.status.color, task.status.color.withOpacity(0.6)],
                    begin: Alignment.topLeft, end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(task.title,
                        style: Theme.of(context).textTheme.headlineSmall
                            ?.copyWith(color: Colors.white)),
                    const SizedBox(height: 10),
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.2),
                            borderRadius: BorderRadius.circular(100),
                          ),
                          child: Text(task.status.label,
                              style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w700)),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.2),
                            borderRadius: BorderRadius.circular(100),
                          ),
                          child: Text(task.priority.label,
                              style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w700)),
                        ),
                        if (task.isOverdue ?? false) ...[const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: AppColors.error.withOpacity(0.3),
                              borderRadius: BorderRadius.circular(100),
                            ),
                            child: const Text('OVERDUE',
                                style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w700)),
                          )],
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 14),
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppColors.surfaceContainerLowest,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _InfoRow('Assigned by', task.assignerName ?? 'Unknown'),
                    _InfoRow('Assigned to', task.assigneeName ?? 'Unknown'),
                    if (task.dueDate != null) _InfoRow('Due date', task.dueDate!),
                    if (task.createdAt != null)
                      _InfoRow('Created', task.createdAt!.substring(0, 10)),
                    if (task.completedAt != null)
                      _InfoRow('Completed', task.completedAt!.substring(0, 10)),
                  ],
                ),
              ),
              if (task.description != null && task.description!.isNotEmpty) ...[const SizedBox(height: 14),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.surfaceContainerLowest,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Description',
                          style: Theme.of(context).textTheme.titleSmall
                              ?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w700)),
                      const SizedBox(height: 8),
                      Text(task.description!,
                          style: Theme.of(context).textTheme.bodyLarge
                              ?.copyWith(color: AppColors.onSurface)),
                    ],
                  ),
                )],
              if (task.completionNotes != null && task.completionNotes!.isNotEmpty) ...[const SizedBox(height: 14),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.secondaryContainer,
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Completion Notes',
                          style: Theme.of(context).textTheme.titleSmall
                              ?.copyWith(color: AppColors.onSecondaryContainer, fontWeight: FontWeight.w700)),
                      const SizedBox(height: 8),
                      Text(task.completionNotes!,
                          style: Theme.of(context).textTheme.bodyLarge
                              ?.copyWith(color: AppColors.onSurface)),
                    ],
                  ),
                )],
              if (task.status == TaskAssignmentStatus.pending ||
                  task.status == TaskAssignmentStatus.inProgress) ...[const SizedBox(height: 14),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.surfaceContainerLowest,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Complete Task',
                          style: Theme.of(context).textTheme.titleSmall
                              ?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w700)),
                      const SizedBox(height: 10),
                      TextField(
                        controller: _notesController,
                        maxLines: 3,
                        decoration: const InputDecoration(
                          hintText: 'Completion notes (optional)...',
                        ),
                      ),
                      const SizedBox(height: 12),
                      GestureDetector(
                        onTap: _isCompleting ? null : _completeTask,
                        child: Container(
                          width: double.infinity, height: 50,
                          decoration: BoxDecoration(
                            gradient: _isCompleting
                                ? null
                                : const LinearGradient(
                                    colors: [AppColors.secondary, Color(0xFF1B6D24)],
                                  ),
                            color: _isCompleting ? AppColors.surfaceContainerHigh : null,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          alignment: Alignment.center,
                          child: _isCompleting
                              ? const SizedBox(
                                  width: 18, height: 18,
                                  child: CircularProgressIndicator(
                                      strokeWidth: 2, color: AppColors.secondary),
                                )
                              : const Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(Icons.check_rounded, color: Colors.white, size: 18),
                                    SizedBox(width: 6),
                                    Text('Mark as Completed',
                                        style: TextStyle(color: Colors.white,
                                            fontWeight: FontWeight.w700, fontSize: 15)),
                                  ],
                                ),
                        ),
                      ),
                    ],
                  ),
                )],
            ],
          ),
        ),
        loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
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
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 110,
            child: Text(label,
                style: Theme.of(context).textTheme.bodySmall
                    ?.copyWith(color: AppColors.onSurfaceVariant)),
          ),
          Expanded(
            child: Text(value,
                style: Theme.of(context).textTheme.bodyMedium
                    ?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w600)),
          ),
        ],
      ),
    );
  }
}
