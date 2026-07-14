enum CampaignStatus {
  draft,
  scheduled,
  active,
  paused,
  completed,
  cancelled;

  String get label => switch (this) {
    draft => 'Draft',
    scheduled => 'Scheduled',
    active => 'Active',
    paused => 'Paused',
    completed => 'Completed',
    cancelled => 'Cancelled',
  };

  static CampaignStatus fromString(String value) {
    return switch (value) {
      'draft' => CampaignStatus.draft,
      'scheduled' => CampaignStatus.scheduled,
      'active' => CampaignStatus.active,
      'paused' => CampaignStatus.paused,
      'completed' => CampaignStatus.completed,
      'cancelled' => CampaignStatus.cancelled,
      _ => CampaignStatus.draft,
    };
  }
}
