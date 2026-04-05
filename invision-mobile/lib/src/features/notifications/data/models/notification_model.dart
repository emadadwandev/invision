import '../../../../core/enums/notification_priority.dart';
import '../../../../core/enums/notification_type.dart';

class NotificationItem {
  const NotificationItem({
    required this.id,
    required this.type,
    required this.priority,
    required this.title,
    required this.isRead,
    this.body,
    this.data,
    this.readAt,
    this.createdAt,
  });

  factory NotificationItem.fromJson(Map<String, dynamic> json) {
    final typeData = json['type'];
    final priorityData = json['priority'];

    return NotificationItem(
      id: json['id'] as int,
      type: NotificationType.fromString(
        typeData is Map ? typeData['value'] as String : typeData as String? ?? 'system',
      ),
      priority: NotificationPriority.fromString(
        priorityData is Map ? priorityData['value'] as String : priorityData as String? ?? 'normal',
      ),
      title: json['title'] as String,
      body: json['body'] as String?,
      data: json['data'] as Map<String, dynamic>?,
      isRead: json['is_read'] as bool? ?? false,
      readAt: json['read_at'] as String?,
      createdAt: json['created_at'] as String?,
    );
  }

  final int id;
  final NotificationType type;
  final NotificationPriority priority;
  final String title;
  final String? body;
  final Map<String, dynamic>? data;
  final bool isRead;
  final String? readAt;
  final String? createdAt;
}
