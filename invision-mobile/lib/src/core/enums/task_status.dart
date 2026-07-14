enum TaskStatus {
  pending,
  inProgress,
  completed,
  verified,
  rejected;

  String get label => switch (this) {
    pending => 'Pending',
    inProgress => 'In Progress',
    completed => 'Completed',
    verified => 'Verified',
    rejected => 'Rejected',
  };

  static TaskStatus fromString(String value) {
    return switch (value) {
      'pending' => TaskStatus.pending,
      'in_progress' => TaskStatus.inProgress,
      'completed' => TaskStatus.completed,
      'verified' => TaskStatus.verified,
      'rejected' => TaskStatus.rejected,
      _ => TaskStatus.pending,
    };
  }
}
