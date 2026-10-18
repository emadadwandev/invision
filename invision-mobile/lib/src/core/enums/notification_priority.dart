import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

enum NotificationPriority {
  low,
  normal,
  high,
  urgent;

  static NotificationPriority fromString(String value) {
    return switch (value) {
      'low' => NotificationPriority.low,
      'normal' => NotificationPriority.normal,
      'high' => NotificationPriority.high,
      'urgent' => NotificationPriority.urgent,
      _ => NotificationPriority.normal,
    };
  }

  String get value => switch (this) {
    NotificationPriority.low => 'low',
    NotificationPriority.normal => 'normal',
    NotificationPriority.high => 'high',
    NotificationPriority.urgent => 'urgent',
  };

  String get label => switch (this) {
    NotificationPriority.low => 'Low',
    NotificationPriority.normal => 'Normal',
    NotificationPriority.high => 'High',
    NotificationPriority.urgent => 'Urgent',
  };

  Color get color => switch (this) {
    NotificationPriority.low => AppColors.outline,
    NotificationPriority.normal => AppColors.primary,
    NotificationPriority.high => AppColors.tertiary,
    NotificationPriority.urgent => AppColors.error,
  };
}
