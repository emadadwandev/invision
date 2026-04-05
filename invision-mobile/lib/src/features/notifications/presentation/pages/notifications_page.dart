import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../data/models/notification_model.dart';
import '../providers/notifications_providers.dart';

class NotificationsPage extends ConsumerWidget {
  const NotificationsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final notificationsAsync = ref.watch(myNotificationsProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Notifications'),
        actions: [
          TextButton(
            onPressed: () async {
              final repo = ref.read(notificationsRepositoryProvider);
              await repo.markAllRead();
              ref.invalidate(myNotificationsProvider);
              ref.invalidate(unreadNotificationCountProvider);
            },
            child: const Text('Mark All Read'),
          ),
        ],
      ),
      body: notificationsAsync.when(
        data: (notifications) => notifications.isEmpty
            ? const Center(child: Text('No notifications.'))
            : RefreshIndicator(
                onRefresh: () async =>
                    ref.invalidate(myNotificationsProvider),
                child: ListView.builder(
                  itemCount: notifications.length,
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  itemBuilder: (context, index) =>
                      _NotificationCard(notification: notifications[index]),
                ),
              ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}

class _NotificationCard extends ConsumerWidget {
  const _NotificationCard({required this.notification});

  final NotificationItem notification;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final theme = Theme.of(context);

    return Card(
      color: notification.isRead ? null : theme.colorScheme.primaryContainer.withAlpha(40),
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: Icon(notification.type.icon, color: notification.type.color),
        title: Text(
          notification.title,
          style: theme.textTheme.titleSmall?.copyWith(
            fontWeight: notification.isRead ? FontWeight.normal : FontWeight.bold,
          ),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (notification.body != null)
              Text(notification.body!,
                  maxLines: 2, overflow: TextOverflow.ellipsis),
            const SizedBox(height: 4),
            Row(
              children: [
                Chip(
                  label: Text(notification.type.label,
                      style: TextStyle(fontSize: 10, color: notification.type.color)),
                  padding: EdgeInsets.zero,
                  materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  visualDensity: VisualDensity.compact,
                  side: BorderSide(color: notification.type.color),
                ),
                const SizedBox(width: 4),
                Chip(
                  label: Text(notification.priority.label,
                      style: TextStyle(fontSize: 10, color: notification.priority.color)),
                  padding: EdgeInsets.zero,
                  materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  visualDensity: VisualDensity.compact,
                  side: BorderSide(color: notification.priority.color),
                ),
              ],
            ),
          ],
        ),
        trailing: notification.isRead
            ? null
            : IconButton(
                icon: const Icon(Icons.mark_email_read, size: 20),
                onPressed: () async {
                  final repo = ref.read(notificationsRepositoryProvider);
                  await repo.markAsRead(notification.id);
                  ref.invalidate(myNotificationsProvider);
                  ref.invalidate(unreadNotificationCountProvider);
                },
              ),
        isThreeLine: true,
      ),
    );
  }
}
