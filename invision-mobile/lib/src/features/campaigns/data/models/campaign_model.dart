import '../../../../core/enums/campaign_status.dart';
import '../../../../core/enums/campaign_type.dart';

class Campaign {
  const Campaign({
    required this.id,
    required this.name,
    required this.type,
    required this.status,
    required this.startDate,
    required this.endDate,
    this.description,
    this.budget,
    this.spent,
    this.budgetUtilization,
    this.creatorName,
    this.tasksCount,
    this.entriesCount,
    this.storesCount,
    this.productsCount,
  });

  factory Campaign.fromJson(Map<String, dynamic> json) {
    return Campaign(
      id: json['id'] as int,
      name: json['name'] as String,
      description: json['description'] as String?,
      type: CampaignType.fromString(json['type'] as String? ?? 'promotion'),
      status:
          CampaignStatus.fromString(json['status'] as String? ?? 'draft'),
      startDate: json['start_date'] as String,
      endDate: json['end_date'] as String,
      budget: (json['budget'] as num?)?.toDouble(),
      spent: (json['spent'] as num?)?.toDouble(),
      budgetUtilization: (json['budget_utilization'] as num?)?.toDouble(),
      creatorName: json['creator'] != null
          ? '${(json['creator'] as Map<String, dynamic>)['first_name']} ${(json['creator'] as Map<String, dynamic>)['last_name']}'
          : null,
      tasksCount: json['tasks_count'] as int?,
      entriesCount: json['entries_count'] as int?,
      storesCount: (json['stores'] as List?)?.length,
      productsCount: (json['products'] as List?)?.length,
    );
  }

  final int id;
  final String name;
  final String? description;
  final CampaignType type;
  final CampaignStatus status;
  final String startDate;
  final String endDate;
  final double? budget;
  final double? spent;
  final double? budgetUtilization;
  final String? creatorName;
  final int? tasksCount;
  final int? entriesCount;
  final int? storesCount;
  final int? productsCount;
}
