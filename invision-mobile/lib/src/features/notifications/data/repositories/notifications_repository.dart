import '../../../../core/constants/app_constants.dart';
import '../../../../core/network/api_client.dart';
import '../models/message_model.dart';
import '../models/notification_model.dart';
import '../models/task_assignment_model.dart';

class NotificationsRepository {
  NotificationsRepository({ApiClient? apiClient})
      : _client = apiClient ?? ApiClient();

  final ApiClient _client;

  // Notifications
  Future<List<NotificationItem>> getMyNotifications() async {
    final response = await _client.dio.get(ApiEndpoints.myNotifications);
    final data = response.data['data'] as List;
    return data
        .map((json) => NotificationItem.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<int> getUnreadCount() async {
    final response = await _client.dio.get(ApiEndpoints.unreadNotificationCount);
    return response.data['data']['count'] as int;
  }

  Future<void> markAsRead(int id) async {
    await _client.dio.post(ApiEndpoints.markNotificationRead(id));
  }

  Future<void> markAllRead() async {
    await _client.dio.post(ApiEndpoints.markAllNotificationsRead);
  }

  Future<void> deleteNotification(int id) async {
    await _client.dio.delete(ApiEndpoints.notification(id));
  }

  // Messages — Inbox
  Future<List<MessageItem>> getInbox({String? search, bool? archived}) async {
    final queryParams = <String, dynamic>{};
    if (search != null && search.isNotEmpty) queryParams['search'] = search;
    if (archived != null) queryParams['archived'] = archived;

    final response = await _client.dio.get(
      ApiEndpoints.inbox,
      queryParameters: queryParams,
    );
    final data = response.data['data'] as List;
    return data
        .map((json) => MessageItem.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<MessageItem> getMessage(int id) async {
    final response = await _client.dio.get(ApiEndpoints.message(id));
    return MessageItem.fromJson(response.data['data'] as Map<String, dynamic>);
  }

  Future<MessageItem> sendMessage({
    required List<int> recipientIds,
    required String subject,
    required String body,
  }) async {
    final response = await _client.dio.post(
      ApiEndpoints.messages,
      data: {
        'recipient_ids': recipientIds,
        'subject': subject,
        'body': body,
      },
    );
    return MessageItem.fromJson(response.data['data'] as Map<String, dynamic>);
  }

  Future<void> markMessageRead(int id) async {
    await _client.dio.post(ApiEndpoints.markMessageRead(id));
  }

  Future<void> archiveMessage(int id) async {
    await _client.dio.post(ApiEndpoints.archiveMessage(id));
  }

  Future<void> deleteMessage(int id) async {
    await _client.dio.delete(ApiEndpoints.message(id));
  }

  // Task Assignments
  Future<List<TaskAssignment>> getMyAssignedTasks({String? status}) async {
    final queryParams = <String, dynamic>{};
    if (status != null && status.isNotEmpty) queryParams['status'] = status;

    final response = await _client.dio.get(
      ApiEndpoints.myAssignedTasks,
      queryParameters: queryParams,
    );
    final data = response.data['data'] as List;
    return data
        .map((json) => TaskAssignment.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<TaskAssignment> getTaskAssignment(int id) async {
    final response = await _client.dio.get(ApiEndpoints.taskAssignment(id));
    return TaskAssignment.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }

  Future<TaskAssignment> completeTask({
    required int id,
    String? proofPhotoPath,
    String? completionNotes,
  }) async {
    final response = await _client.dio.post(
      ApiEndpoints.completeTaskAssignment(id),
      data: {
        if (proofPhotoPath != null) 'proof_photo_path': proofPhotoPath,
        if (completionNotes != null) 'completion_notes': completionNotes,
      },
    );
    return TaskAssignment.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }
}
