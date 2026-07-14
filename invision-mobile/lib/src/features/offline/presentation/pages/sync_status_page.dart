import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/services/sync_engine.dart';
import '../../../../core/theme/app_theme.dart';
import '../providers/offline_providers.dart';

class SyncStatusPage extends ConsumerWidget {
  const SyncStatusPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final syncAsync = ref.watch(syncStatusProvider);
    final isOnline = ref.watch(isOnlineProvider);
    final pendingAsync = ref.watch(pendingActionsCountProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Sync Status',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
        actions: [
          GestureDetector(
            onTap: () {
              final engine = ref.read(syncEngineProvider);
              engine.syncNow();
              ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Sync triggered')));
            },
            child: Container(
              margin: const EdgeInsets.only(right: 12),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: AppColors.primary,
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.sync_rounded, color: Colors.white, size: 16),
                  SizedBox(width: 4),
                  Text('Sync', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w700)),
                ],
              ),
            ),
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Connection status
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppColors.surfaceContainerLowest,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
            ),
            child: Row(
              children: [
                Container(
                  width: 44, height: 44,
                  decoration: BoxDecoration(
                    color: isOnline
                        ? AppColors.secondary.withOpacity(0.1)
                        : AppColors.errorContainer,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(
                    isOnline ? Icons.wifi_rounded : Icons.wifi_off_rounded,
                    color: isOnline ? AppColors.secondary : AppColors.error,
                    size: 24,
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(isOnline ? 'Online' : 'Offline',
                          style: TextStyle(
                              fontWeight: FontWeight.w700,
                              color: isOnline ? AppColors.secondary : AppColors.error)),
                      const SizedBox(height: 2),
                      Text(
                        isOnline
                            ? 'Connected to the server'
                            : 'Changes will be saved locally and synced when online',
                        style: const TextStyle(
                            fontSize: 12, color: AppColors.onSurfaceVariant),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          // Sync status card
          syncAsync.when(
            data: (status) => _SyncInfoCard(status: status),
            loading: () => Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppColors.surfaceContainerLowest,
                borderRadius: BorderRadius.circular(14),
              ),
              child: const Center(child: CircularProgressIndicator(color: AppColors.primary)),
            ),
            error: (_, __) => Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.surfaceContainerLowest,
                borderRadius: BorderRadius.circular(14),
              ),
              child: const Row(
                children: [
                  Icon(Icons.info_outline_rounded, color: AppColors.outline),
                  SizedBox(width: 10),
                  Text('No sync activity yet',
                      style: TextStyle(color: AppColors.onSurfaceVariant)),
                ],
              ),
            ),
          ),
          const SizedBox(height: 12),
          // Pending actions
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppColors.surfaceContainerLowest,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
            ),
            child: Row(
              children: [
                const Icon(Icons.pending_actions_rounded, size: 32, color: AppColors.primary),
                const SizedBox(width: 14),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Pending Actions',
                          style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.onSurface)),
                      Text('Actions waiting to be synced',
                          style: TextStyle(fontSize: 12, color: AppColors.onSurfaceVariant)),
                    ],
                  ),
                ),
                pendingAsync.when(
                  data: (count) => Text(
                    '$count',
                    style: TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                      color: count > 0 ? AppColors.tertiary : AppColors.secondary,
                    ),
                  ),
                  loading: () => const SizedBox.square(
                      dimension: 20, child: CircularProgressIndicator(strokeWidth: 2)),
                  error: (_, __) => const Text('—'),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          // Force Sync button
          GestureDetector(
            onTap: isOnline
                ? () => ref.read(syncEngineProvider).syncNow()
                : null,
            child: Container(
              height: 50,
              decoration: BoxDecoration(
                gradient: isOnline
                    ? const LinearGradient(
                        colors: [AppColors.primary, AppColors.primaryContainer])
                    : null,
                color: isOnline ? null : AppColors.surfaceContainerHigh,
                borderRadius: BorderRadius.circular(12),
              ),
              alignment: Alignment.center,
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.cloud_upload_rounded,
                      color: isOnline ? Colors.white : AppColors.outline, size: 18),
                  const SizedBox(width: 6),
                  Text('Force Sync Now',
                      style: TextStyle(
                          color: isOnline ? Colors.white : AppColors.outline,
                          fontWeight: FontWeight.w700)),
                ],
              ),
            ),
          ),
          const SizedBox(height: 10),
          GestureDetector(
            onTap: () async {
              final db = ref.read(offlineDatabaseProvider);
              await db.resetFailedActions();
              ref.invalidate(pendingActionsCountProvider);
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Failed actions reset for retry')),
                );
              }
            },
            child: Container(
              height: 50,
              decoration: BoxDecoration(
                color: AppColors.surfaceContainerLow,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.outlineVariant),
              ),
              alignment: Alignment.center,
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.refresh_rounded, size: 16, color: AppColors.primary),
                  SizedBox(width: 6),
                  Text('Retry Failed Actions',
                      style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.w700)),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _SyncInfoCard extends StatelessWidget {
  const _SyncInfoCard({required this.status});

  final SyncStatus status;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              if (status.isSyncing) ...[
                const SizedBox.square(
                    dimension: 20,
                    child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.primary)),
                const SizedBox(width: 12),
                const Text('Syncing...',
                    style: TextStyle(color: AppColors.onSurfaceVariant)),
              ] else ...[
                Icon(Icons.check_circle_rounded,
                    color: status.hasPending ? AppColors.tertiary : AppColors.secondary),
                const SizedBox(width: 12),
                Text(status.hasPending ? 'Pending changes' : 'All synced',
                    style: const TextStyle(fontWeight: FontWeight.w700, color: AppColors.onSurface)),
              ],
            ],
          ),
          const SizedBox(height: 12),
          _InfoRow(
            label: 'Connection',
            value: status.isOnline ? 'Online' : 'Offline',
            valueColor: status.isOnline ? AppColors.secondary : AppColors.error,
          ),
          _InfoRow(
            label: 'Pending Actions',
            value: '${status.pendingActions}',
            valueColor: status.pendingActions > 0 ? AppColors.tertiary : AppColors.secondary,
          ),
        ],
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow({
    required this.label,
    required this.value,
    this.valueColor,
  });

  final String label;
  final String value;
  final Color? valueColor;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: Theme.of(context).textTheme.bodyMedium),
          Text(
            value,
            style: TextStyle(
              fontWeight: FontWeight.bold,
              color: valueColor,
            ),
          ),
        ],
      ),
    );
  }
}
