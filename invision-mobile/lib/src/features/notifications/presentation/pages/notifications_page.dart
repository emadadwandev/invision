import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/theme/app_theme.dart';
import '../../data/models/notification_model.dart';
import '../providers/notifications_providers.dart';

class NotificationsPage extends ConsumerWidget {
  const NotificationsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final notificationsAsync = ref.watch(myNotificationsProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('My Notifications',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
        actions: [
          GestureDetector(
            onTap: () async {
              final repo = ref.read(notificationsRepositoryProvider);
              await repo.markAllRead();
              ref.invalidate(myNotificationsProvider);
              ref.invalidate(unreadNotificationCountProvider);
            },
            child: Container(
              margin: const EdgeInsets.only(right: 12),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: AppColors.surfaceContainerLow,
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Text('Mark All Read',
                  style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.w600, fontSize: 12)),
            ),
          ),
        ],
      ),
      body: notificationsAsync.when(
        data: (notifications) => notifications.isEmpty
            ? Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      width: 64, height: 64,
                      decoration: BoxDecoration(
                          color: AppColors.surfaceContainerHigh,
                          borderRadius: BorderRadius.circular(16)),
                      child: const Icon(Icons.notifications_none_rounded, size: 32, color: AppColors.outline),
                    ),
                    const SizedBox(height: 12),
                    Text('No notifications.',
                        style: Theme.of(context).textTheme.bodyLarge
                            ?.copyWith(color: AppColors.onSurfaceVariant)),
                  ],
                ),
              )
            : RefreshIndicator(
                onRefresh: () async => ref.invalidate(myNotificationsProvider),
                child: ListView.builder(
                  itemCount: notifications.length,
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  itemBuilder: (context, index) =>
                      _NotificationCard(notification: notifications[index]),
                ),
              ),
        loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
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

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: notification.isRead
            ? AppColors.surfaceContainerLowest
            : AppColors.surfaceContainerLow,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 36, height: 36,
            decoration: BoxDecoration(
              color: AppColors.surfaceContainerHigh,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(notification.type.icon, color: AppColors.primary, size: 18),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  notification.title,
                  style: Theme.of(context).textTheme.titleSmall?.copyWith(
                        color: AppColors.onSurface,
                        fontWeight: notification.isRead ? FontWeight.w500 : FontWeight.w700,
                      ),
                ),
                if (notification.body != null) ...[const SizedBox(height: 4),
                  Text(notification.body!,
                      maxLines: 2, overflow: TextOverflow.ellipsis,
                      style: Theme.of(context).textTheme.bodySmall
                          ?.copyWith(color: AppColors.onSurfaceVariant))],
                const SizedBox(height: 6),
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.surfaceContainerHigh,
                        borderRadius: BorderRadius.circular(100),
                      ),
                      child: Text(notification.type.label,
                          style: TextStyle(fontSize: 9, color: notification.type.color, fontWeight: FontWeight.w700)),
                    ),
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.surfaceContainerHigh,
                        borderRadius: BorderRadius.circular(100),
                      ),
                      child: Text(notification.priority.label,
                          style: TextStyle(fontSize: 9, color: notification.priority.color, fontWeight: FontWeight.w700)),
                    ),
                  ],
                ),
              ],
            ),
          ),
          if (!notification.isRead)
            GestureDetector(
              onTap: () async {
                final repo = ref.read(notificationsRepositoryProvider);
                await repo.markAsRead(notification.id);
                ref.invalidate(myNotificationsProvider);
                ref.invalidate(unreadNotificationCountProvider);
              },
              child: Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: AppColors.surfaceContainerHigh,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(Icons.mark_email_read_rounded, size: 16, color: AppColors.primary),
              ),
            ),
        ],
      ),
    );
  }
}
