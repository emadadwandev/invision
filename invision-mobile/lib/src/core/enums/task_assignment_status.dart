import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

enum TaskAssignmentStatus {
  pending,
  inProgress,
  completed,
  verified,
  rejected;

  static TaskAssignmentStatus fromString(String value) {
    return switch (value) {
      'pending' => TaskAssignmentStatus.pending,
      'in_progress' => TaskAssignmentStatus.inProgress,
      'completed' => TaskAssignmentStatus.completed,
      'verified' => TaskAssignmentStatus.verified,
      'rejected' => TaskAssignmentStatus.rejected,
      _ => TaskAssignmentStatus.pending,
    };
  }

  String get value => switch (this) {
    TaskAssignmentStatus.pending => 'pending',
    TaskAssignmentStatus.inProgress => 'in_progress',
    TaskAssignmentStatus.completed => 'completed',
    TaskAssignmentStatus.verified => 'verified',
    TaskAssignmentStatus.rejected => 'rejected',
  };

  String get label => switch (this) {
    TaskAssignmentStatus.pending => 'Pending',
    TaskAssignmentStatus.inProgress => 'In Progress',
    TaskAssignmentStatus.completed => 'Completed',
    TaskAssignmentStatus.verified => 'Verified',
    TaskAssignmentStatus.rejected => 'Rejected',
  };

  Color get color => switch (this) {
    TaskAssignmentStatus.pending => AppColors.outline,
    TaskAssignmentStatus.inProgress => AppColors.tertiary,
    TaskAssignmentStatus.completed => AppColors.secondary,
    TaskAssignmentStatus.verified => AppColors.primary,
    TaskAssignmentStatus.rejected => AppColors.error,
  };
}
