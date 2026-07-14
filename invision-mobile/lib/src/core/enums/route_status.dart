enum RouteStatus {
  draft,
  published,
  inProgress,
  completed,
  cancelled;

  String get label => switch (this) {
    draft => 'Draft',
    published => 'Published',
    inProgress => 'In Progress',
    completed => 'Completed',
    cancelled => 'Cancelled',
  };

  static RouteStatus fromString(String value) {
    return switch (value) {
      'draft' => RouteStatus.draft,
      'published' => RouteStatus.published,
      'in_progress' => RouteStatus.inProgress,
      'completed' => RouteStatus.completed,
      'cancelled' => RouteStatus.cancelled,
      _ => RouteStatus.draft,
    };
  }
}
