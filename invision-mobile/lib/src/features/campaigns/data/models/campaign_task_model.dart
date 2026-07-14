import '../../../../core/enums/task_status.dart';

class CampaignTask {
  const CampaignTask({
    required this.id,
    required this.campaignId,
    required this.storeId,
    required this.assignedTo,
    required this.status,
    this.campaignName,
    this.storeName,
    this.assignedUserName,
    this.instructions,
    this.completedAt,
    this.verifiedAt,
    this.verifierName,
    this.rejectionReason,
    this.photos = const [],
  });

  factory CampaignTask.fromJson(Map<String, dynamic> json) {
    return CampaignTask(
      id: json['id'] as int,
      campaignId: json['campaign_id'] as int,
      storeId: json['store_id'] as int,
      assignedTo: json['assigned_to'] as int,
      status: TaskStatus.fromString(json['status'] as String? ?? 'pending'),
      campaignName: json['campaign'] != null
          ? (json['campaign'] as Map<String, dynamic>)['name'] as String?
          : null,
      storeName: json['store'] != null
          ? (json['store'] as Map<String, dynamic>)['name'] as String?
          : null,
      assignedUserName: json['assigned_user'] != null
          ? '${(json['assigned_user'] as Map<String, dynamic>)['first_name']} ${(json['assigned_user'] as Map<String, dynamic>)['last_name']}'
          : null,
      instructions: json['instructions'] as String?,
      completedAt: json['completed_at'] as String?,
      verifiedAt: json['verified_at'] as String?,
      verifierName: json['verifier'] != null
          ? '${(json['verifier'] as Map<String, dynamic>)['first_name']} ${(json['verifier'] as Map<String, dynamic>)['last_name']}'
          : null,
      rejectionReason: json['rejection_reason'] as String?,
      photos: json['photos'] != null
          ? (json['photos'] as List)
              .map((p) =>
                  TaskPhoto.fromJson(p as Map<String, dynamic>))
              .toList()
          : [],
    );
  }

  final int id;
  final int campaignId;
  final int storeId;
  final int assignedTo;
  final TaskStatus status;
  final String? campaignName;
  final String? storeName;
  final String? assignedUserName;
  final String? instructions;
  final String? completedAt;
  final String? verifiedAt;
  final String? verifierName;
  final String? rejectionReason;
  final List<TaskPhoto> photos;
}

class TaskPhoto {
  const TaskPhoto({
    required this.id,
    required this.photoPath,
    this.caption,
    this.type,
  });

  factory TaskPhoto.fromJson(Map<String, dynamic> json) {
    return TaskPhoto(
      id: json['id'] as int,
      photoPath: json['photo_path'] as String,
      caption: json['caption'] as String?,
      type: json['type'] as String?,
    );
  }

  final int id;
  final String photoPath;
  final String? caption;
  final String? type;
}
