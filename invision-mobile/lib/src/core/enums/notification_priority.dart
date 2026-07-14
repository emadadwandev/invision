import 'package:flutter/material.dart';

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
    NotificationPriority.low => Colors.grey,
    NotificationPriority.normal => Colors.blue,
    NotificationPriority.high => Colors.orange,
    NotificationPriority.urgent => Colors.red,
  };
}
