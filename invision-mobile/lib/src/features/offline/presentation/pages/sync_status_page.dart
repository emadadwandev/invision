import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/services/sync_engine.dart';
import '../providers/offline_providers.dart';

class SyncStatusPage extends ConsumerWidget {
  const SyncStatusPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final syncAsync = ref.watch(syncStatusProvider);
    final isOnline = ref.watch(isOnlineProvider);
    final pendingAsync = ref.watch(pendingActionsCountProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Sync Status'),
        actions: [
          IconButton(
            icon: const Icon(Icons.sync),
            tooltip: 'Sync Now',
            onPressed: () {
              final engine = ref.read(syncEngineProvider);
              engine.syncNow();
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Sync triggered')),
              );
            },
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Connection status card
          Card(
            child: ListTile(
              leading: Icon(
                isOnline ? Icons.wifi : Icons.wifi_off,
                color: isOnline ? Colors.green : Colors.red,
                size: 32,
              ),
              title: Text(isOnline ? 'Online' : 'Offline'),
              subtitle: Text(
                isOnline
                    ? 'Connected to the server'
                    : 'Changes will be saved locally and synced when online',
              ),
            ),
          ),
          const SizedBox(height: 12),

          // Sync status card
          syncAsync.when(
            data: (status) => _SyncInfoCard(status: status),
            loading: () => const Card(
              child: Padding(
                padding: EdgeInsets.all(20),
                child: Center(child: CircularProgressIndicator()),
              ),
            ),
            error: (_, __) => const Card(
              child: ListTile(
                leading: Icon(Icons.info_outline),
                title: Text('No sync activity yet'),
              ),
            ),
          ),
          const SizedBox(height: 12),

          // Pending actions
          Card(
            child: ListTile(
              leading: const Icon(Icons.pending_actions, size: 32),
              title: const Text('Pending Actions'),
              trailing: pendingAsync.when(
                data: (count) => Text(
                  '$count',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: count > 0 ? Colors.orange : Colors.green,
                  ),
                ),
                loading: () =>
                    const SizedBox.square(dimension: 20, child: CircularProgressIndicator(strokeWidth: 2)),
                error: (_, __) => const Text('—'),
              ),
              subtitle: const Text('Actions waiting to be synced'),
            ),
          ),
          const SizedBox(height: 24),

          // Actions
          FilledButton.icon(
            onPressed: isOnline
                ? () {
                    final engine = ref.read(syncEngineProvider);
                    engine.syncNow();
                  }
                : null,
            icon: const Icon(Icons.cloud_upload),
            label: const Text('Force Sync Now'),
          ),
          const SizedBox(height: 8),
          OutlinedButton.icon(
            onPressed: () async {
              final db = ref.read(offlineDatabaseProvider);
              await db.resetFailedActions();
              ref.invalidate(pendingActionsCountProvider);
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Failed actions reset for retry')),
                );
              }
            },
            icon: const Icon(Icons.refresh),
            label: const Text('Retry Failed Actions'),
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
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                if (status.isSyncing) ...[
                  const SizedBox.square(
                    dimension: 20,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  ),
                  const SizedBox(width: 12),
                  const Text('Syncing...'),
                ] else ...[
                  Icon(
                    Icons.check_circle,
                    color: status.hasPending ? Colors.orange : Colors.green,
                  ),
                  const SizedBox(width: 12),
                  Text(
                    status.hasPending ? 'Pending changes' : 'All synced',
                  ),
                ],
              ],
            ),
            const SizedBox(height: 12),
            _InfoRow(
              label: 'Connection',
              value: status.isOnline ? 'Online' : 'Offline',
              valueColor: status.isOnline ? Colors.green : Colors.red,
            ),
            _InfoRow(
              label: 'Pending Actions',
              value: '${status.pendingActions}',
              valueColor:
                  status.pendingActions > 0 ? Colors.orange : Colors.green,
            ),
          ],
        ),
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
