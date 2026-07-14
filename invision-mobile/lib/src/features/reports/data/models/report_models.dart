class ReportData {
  final String title;
  final String? period;
  final String generated;
  final List<Map<String, dynamic>> rows;

  ReportData({
    required this.title,
    this.period,
    required this.generated,
    required this.rows,
  });

  factory ReportData.fromJson(Map<String, dynamic> json) {
    return ReportData(
      title: json['title'] ?? '',
      period: json['period'],
      generated: json['generated'] ?? '',
      rows: (json['rows'] as List<dynamic>?)
              ?.map((r) => Map<String, dynamic>.from(r as Map))
              .toList() ??
          [],
    );
  }

  List<String> get columns => rows.isNotEmpty ? rows.first.keys.toList() : [];
}

class ReportEntity {
  final String key;
  final String label;
  final List<String> columns;
  final List<String> groupByOptions;
  final List<String> aggregations;

  ReportEntity({
    required this.key,
    required this.label,
    required this.columns,
    required this.groupByOptions,
    required this.aggregations,
  });

  factory ReportEntity.fromJson(String key, Map<String, dynamic> json) {
    return ReportEntity(
      key: key,
      label: json['label'] ?? key,
      columns: List<String>.from(json['columns'] ?? []),
      groupByOptions: List<String>.from(json['group_by_options'] ?? []),
      aggregations: List<String>.from(json['aggregations'] ?? []),
    );
  }
}

enum FixedReportType {
  sellThrough('sell-through', 'Sell-Through', 'POS sell-through by product'),
  sellOut('sell-out', 'Sell-Out', 'POS sell-out by product'),
  sellIn('sell-in', 'Sell-In', 'Delivered orders by product'),
  stockMovement('stock-movement', 'Stock Movement', 'Stock ins/outs/adjustments'),
  vendorRanking('vendor-ranking', 'Vendor Ranking', 'Stores ranked by sales'),
  salesRepPerformance('sales-rep-performance', 'Sales Rep Performance', 'Rep rankings with route completion');

  const FixedReportType(this.slug, this.label, this.description);
  final String slug;
  final String label;
  final String description;
}
