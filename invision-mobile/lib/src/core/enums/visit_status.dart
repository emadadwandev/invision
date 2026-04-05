enum VisitStatus {
  pending,
  checkedIn,
  completed,
  skipped;

  String get label => switch (this) {
    pending => 'Pending',
    checkedIn => 'Checked In',
    completed => 'Completed',
    skipped => 'Skipped',
  };

  static VisitStatus fromString(String value) {
    return switch (value) {
      'pending' => VisitStatus.pending,
      'checked_in' => VisitStatus.checkedIn,
      'completed' => VisitStatus.completed,
      'skipped' => VisitStatus.skipped,
      _ => VisitStatus.pending,
    };
  }
}
