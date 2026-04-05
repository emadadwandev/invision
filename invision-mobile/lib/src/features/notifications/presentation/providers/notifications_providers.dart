import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/network/api_client.dart';
import '../../data/models/message_model.dart';
import '../../data/models/notification_model.dart';
import '../../data/models/task_assignment_model.dart';
import '../../data/repositories/notifications_repository.dart';

final notificationsRepositoryProvider = Provider(
  (ref) => NotificationsRepository(apiClient: ref.watch(apiClientProvider)),
);

// My Notifications
final myNotificationsProvider =
    FutureProvider.autoDispose<List<NotificationItem>>(
  (ref) {
    final repo = ref.watch(notificationsRepositoryProvider);
    return repo.getMyNotifications();
  },
);

// Unread count
final unreadNotificationCountProvider = FutureProvider.autoDispose<int>(
  (ref) {
    final repo = ref.watch(notificationsRepositoryProvider);
    return repo.getUnreadCount();
  },
);

// Inbox
final inboxProvider =
    FutureProvider.autoDispose.family<List<MessageItem>, InboxFilter>(
  (ref, filter) {
    final repo = ref.watch(notificationsRepositoryProvider);
    return repo.getInbox(search: filter.search, archived: filter.archived);
  },
);

// Message detail
final messageDetailProvider =
    FutureProvider.autoDispose.family<MessageItem, int>(
  (ref, id) {
    final repo = ref.watch(notificationsRepositoryProvider);
    return repo.getMessage(id);
  },
);

// My Assigned Tasks
final myAssignedTasksProvider =
    FutureProvider.autoDispose.family<List<TaskAssignment>, String?>(
  (ref, status) {
    final repo = ref.watch(notificationsRepositoryProvider);
    return repo.getMyAssignedTasks(status: status);
  },
);

// Task detail
final taskAssignmentDetailProvider =
    FutureProvider.autoDispose.family<TaskAssignment, int>(
  (ref, id) {
    final repo = ref.watch(notificationsRepositoryProvider);
    return repo.getTaskAssignment(id);
  },
);

// Filter classes
class InboxFilter {
  const InboxFilter({this.search, this.archived});

  final String? search;
  final bool? archived;

  InboxFilter copyWith({String? search, bool? archived}) {
    return InboxFilter(
      search: search ?? this.search,
      archived: archived ?? this.archived,
    );
  }

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is InboxFilter &&
          search == other.search &&
          archived == other.archived;

  @override
  int get hashCode => Object.hash(search, archived);
}
