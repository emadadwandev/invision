enum CampaignType {
  promotion,
  discount,
  sampling,
  display,
  posm,
  buyGetFree;

  String get label => switch (this) {
    promotion => 'Promotion',
    discount => 'Discount',
    sampling => 'Sampling',
    display => 'Display',
    posm => 'POSM',
    buyGetFree => 'Buy & Get Free',
  };

  static CampaignType fromString(String value) {
    return switch (value) {
      'promotion' => CampaignType.promotion,
      'discount' => CampaignType.discount,
      'sampling' => CampaignType.sampling,
      'display' => CampaignType.display,
      'posm' => CampaignType.posm,
      'buy_get_free' => CampaignType.buyGetFree,
      _ => CampaignType.promotion,
    };
  }
}
