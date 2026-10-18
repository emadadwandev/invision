import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

enum NotificationType {
  task,
  message,
  alert,
  announcement,
  system;

  static NotificationType fromString(String value) {
    return switch (value) {
      'task' => NotificationType.task,
      'message' => NotificationType.message,
      'alert' => NotificationType.alert,
      'announcement' => NotificationType.announcement,
      'system' => NotificationType.system,
      _ => NotificationType.system,
    };
  }

  String get value => switch (this) {
    NotificationType.task => 'task',
    NotificationType.message => 'message',
    NotificationType.alert => 'alert',
    NotificationType.announcement => 'announcement',
    NotificationType.system => 'system',
  };

  String get label => switch (this) {
    NotificationType.task => 'Task',
    NotificationType.message => 'Message',
    NotificationType.alert => 'Alert',
    NotificationType.announcement => 'Announcement',
    NotificationType.system => 'System',
  };

  Color get color => switch (this) {
    NotificationType.task => AppColors.primary,
    NotificationType.message => AppColors.secondary,
    NotificationType.alert => AppColors.error,
    NotificationType.announcement => AppColors.tertiary,
    NotificationType.system => AppColors.outline,
  };

  IconData get icon => switch (this) {
    NotificationType.task => Icons.task_alt,
    NotificationType.message => Icons.message,
    NotificationType.alert => Icons.warning,
    NotificationType.announcement => Icons.campaign,
    NotificationType.system => Icons.settings,
  };
}
