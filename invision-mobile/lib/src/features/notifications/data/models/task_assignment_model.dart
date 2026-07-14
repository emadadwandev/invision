import '../../../../core/enums/notification_priority.dart';
import '../../../../core/enums/task_assignment_status.dart';

class TaskAssignment {
  const TaskAssignment({
    required this.id,
    required this.title,
    required this.priority,
    required this.status,
    this.description,
    this.assignerName,
    this.assignerId,
    this.assigneeName,
    this.assigneeId,
    this.dueDate,
    this.isOverdue,
    this.proofPhotoPath,
    this.completionNotes,
    this.completedAt,
    this.createdAt,
    this.updatedAt,
  });

  factory TaskAssignment.fromJson(Map<String, dynamic> json) {
    final priorityData = json['priority'];
    final statusData = json['status'];
    final assigner = json['assigner'] as Map<String, dynamic>?;
    final assignee = json['assignee'] as Map<String, dynamic>?;

    return TaskAssignment(
      id: json['id'] as int,
      title: json['title'] as String,
      description: json['description'] as String?,
      priority: NotificationPriority.fromString(
        priorityData is Map ? priorityData['value'] as String : priorityData as String? ?? 'normal',
      ),
      status: TaskAssignmentStatus.fromString(
        statusData is Map ? statusData['value'] as String : statusData as String? ?? 'pending',
      ),
      assignerId: assigner?['id'] as int?,
      assignerName: assigner?['name'] as String?,
      assigneeId: assignee?['id'] as int?,
      assigneeName: assignee?['name'] as String?,
      dueDate: json['due_date'] as String?,
      isOverdue: json['is_overdue'] as bool? ?? false,
      proofPhotoPath: json['proof_photo_path'] as String?,
      completionNotes: json['completion_notes'] as String?,
      completedAt: json['completed_at'] as String?,
      createdAt: json['created_at'] as String?,
      updatedAt: json['updated_at'] as String?,
    );
  }

  final int id;
  final String title;
  final String? description;
  final NotificationPriority priority;
  final TaskAssignmentStatus status;
  final int? assignerId;
  final String? assignerName;
  final int? assigneeId;
  final String? assigneeName;
  final String? dueDate;
  final bool? isOverdue;
  final String? proofPhotoPath;
  final String? completionNotes;
  final String? completedAt;
  final String? createdAt;
  final String? updatedAt;
}
