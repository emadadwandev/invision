class CalendarEventModel {
  CalendarEventModel({
    required this.id,
    required this.title,
    this.description,
    required this.startAt,
    this.endAt,
    this.allDay = false,
    required this.type,
    this.color,
  });

  factory CalendarEventModel.fromJson(Map<String, dynamic> json) =>
      CalendarEventModel(
        id: json['id'] as int,
        title: json['title'] as String,
        description: json['description'] as String?,
        startAt: DateTime.parse(json['start_at'] as String),
        endAt: json['end_at'] != null
            ? DateTime.parse(json['end_at'] as String)
            : null,
        allDay: json['all_day'] as bool? ?? false,
        type: json['type'] as String? ?? 'other',
        color: json['color'] as String?,
      );

  final int id;
  final String title;
  final String? description;
  final DateTime startAt;
  final DateTime? endAt;
  final bool allDay;
  final String type;
  final String? color;
}

class HolidayModel {
  HolidayModel({
    required this.id,
    required this.name,
    required this.date,
    required this.type,
    this.description,
    this.isRecurring = false,
  });

  factory HolidayModel.fromJson(Map<String, dynamic> json) => HolidayModel(
        id: json['id'] as int,
        name: json['name'] as String,
        date: DateTime.parse(json['date'] as String),
        type: json['type'] as String? ?? 'public',
        description: json['description'] as String?,
        isRecurring: json['is_recurring'] as bool? ?? false,
      );

  final int id;
  final String name;
  final DateTime date;
  final String type;
  final String? description;
  final bool isRecurring;
}

class SalesAreaModel {
  SalesAreaModel({
    required this.id,
    required this.name,
    this.description,
    this.parentId,
    this.managerId,
    this.managerName,
    this.isActive = true,
    this.children = const [],
    this.storeCount = 0,
  });

  factory SalesAreaModel.fromJson(Map<String, dynamic> json) => SalesAreaModel(
        id: json['id'] as int,
        name: json['name'] as String,
        description: json['description'] as String?,
        parentId: json['parent_id'] as int?,
        managerId: json['manager_id'] as int?,
        managerName: json['manager'] != null
            ? '${json['manager']['first_name']} ${json['manager']['last_name']}'
            : null,
        isActive: json['is_active'] as bool? ?? true,
        children: json['children'] != null
            ? (json['children'] as List)
                .map((c) =>
                    SalesAreaModel.fromJson(c as Map<String, dynamic>))
                .toList()
            : [],
        storeCount: json['stores'] != null
            ? (json['stores'] as List).length
            : json['stores_count'] as int? ?? 0,
      );

  final int id;
  final String name;
  final String? description;
  final int? parentId;
  final int? managerId;
  final String? managerName;
  final bool isActive;
  final List<SalesAreaModel> children;
  final int storeCount;
}
