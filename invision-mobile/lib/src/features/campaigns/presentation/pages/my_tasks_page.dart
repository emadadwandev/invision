import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/enums/task_status.dart';
import '../../../../core/theme/app_theme.dart';
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
    final tt = Theme.of(context).textTheme;
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('My Tasks', style: tt.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
        actions: [
          IconButton(
            icon: Container(
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                color: _statusFilter != null ? AppColors.primaryContainer.withOpacity(0.15) : Colors.transparent,
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Icon(Icons.filter_list_rounded, color: AppColors.onSurface),
            ),
            onPressed: () {
              showModalBottomSheet(
                context: context,
                builder: (_) => SafeArea(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const SizedBox(height: 8),
                      Container(
                        width: 40, height: 4,
                        decoration: BoxDecoration(
                          color: AppColors.outlineVariant,
                          borderRadius: BorderRadius.circular(2),
                        ),
                      ),
                      const SizedBox(height: 16),
                      for (final entry in {
                        null: 'All Tasks',
                        'pending': 'Pending',
                        'in_progress': 'In Progress',
                        'completed': 'Completed',
                      }.entries)
                        ListTile(
                          title: Text(entry.value),
                          trailing: _statusFilter == entry.key
                              ? const Icon(Icons.check_rounded, color: AppColors.primary)
                              : null,
                          onTap: () {
                            setState(() => _statusFilter = entry.key);
                            Navigator.pop(context);
                          },
                        ),
                      const SizedBox(height: 8),
                    ],
                  ),
                ),
              );
            },
          ),
        ],
      ),
      body: tasksAsync.when(
        data: (tasks) => tasks.isEmpty
            ? Center(
                child: Column(mainAxisSize: MainAxisSize.min, children: [
                  Container(
                    width: 64, height: 64,
                    decoration: BoxDecoration(
                      color: AppColors.surfaceContainerHigh,
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: const Icon(Icons.task_alt_rounded, size: 32, color: AppColors.outline),
                  ),
                  const SizedBox(height: 12),
                  Text('No tasks found.',
                      style: tt.bodyLarge?.copyWith(color: AppColors.onSurfaceVariant)),
                ]),
              )
            : RefreshIndicator(
                color: AppColors.primary,
                onRefresh: () async => ref.invalidate(myTasksProvider(_statusFilter)),
                child: ListView.builder(
                  itemCount: tasks.length,
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
                  itemBuilder: (context, index) => _TaskCard(task: tasks[index]),
                ),
              ),
        loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
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
      TaskStatus.pending => AppColors.onSurfaceVariant,
      TaskStatus.inProgress => AppColors.tertiary,
      TaskStatus.completed => AppColors.primaryContainer,
      TaskStatus.verified => AppColors.secondary,
      TaskStatus.rejected => AppColors.error,
    };
  }

  Color _statusBg(TaskStatus status) {
    return switch (status) {
      TaskStatus.pending => AppColors.surfaceContainerHigh,
      TaskStatus.inProgress => AppColors.tertiaryContainer.withOpacity(0.3),
      TaskStatus.completed => AppColors.surfaceContainerLow,
      TaskStatus.verified => AppColors.secondaryContainer,
      TaskStatus.rejected => AppColors.errorContainer,
    };
  }

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest,
        borderRadius: BorderRadius.circular(14),
        border: Border(
          left: BorderSide(color: _statusColor(task.status), width: 3),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  task.campaignName ?? 'Task #${task.id}',
                  style: tt.titleSmall?.copyWith(
                      color: AppColors.onSurface, fontWeight: FontWeight.w700),
                ),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: _statusBg(task.status),
                  borderRadius: BorderRadius.circular(100),
                ),
                child: Text(
                  task.status.label,
                  style: TextStyle(
                      color: _statusColor(task.status),
                      fontSize: 10, fontWeight: FontWeight.w700),
                ),
              ),
            ],
          ),
          if (task.storeName != null)
            Padding(
              padding: const EdgeInsets.only(top: 3),
              child: Text(task.storeName!,
                  style: tt.bodySmall?.copyWith(color: AppColors.primary)),
            ),
          if (task.instructions != null) ...[const SizedBox(height: 4),
            Text(task.instructions!,
                style: tt.bodySmall?.copyWith(color: AppColors.onSurfaceVariant),
                maxLines: 2, overflow: TextOverflow.ellipsis)],
        ],
      ),
    );
  }
}
