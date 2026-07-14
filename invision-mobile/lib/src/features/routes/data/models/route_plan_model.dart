import '../../../../core/enums/route_status.dart';
import '../../../stores/data/models/store_model.dart';

class RoutePlan {
  const RoutePlan({
    required this.id,
    required this.name,
    required this.assignedTo,
    required this.frequency,
    required this.startDate,
    required this.status,
    required this.totalStores,
    this.description,
    this.endDate,
    this.assignedUserName,
    this.stores = const [],
  });

  factory RoutePlan.fromJson(Map<String, dynamic> json) {
    return RoutePlan(
      id: json['id'] as int,
      name: json['name'] as String,
      description: json['description'] as String?,
      assignedTo: json['assigned_to'] as int,
      assignedUserName: json['assigned_user'] != null
          ? '${(json['assigned_user'] as Map<String, dynamic>)['first_name']} ${(json['assigned_user'] as Map<String, dynamic>)['last_name']}'
          : null,
      frequency: json['frequency'] as String? ?? 'daily',
      startDate: json['start_date'] as String,
      endDate: json['end_date'] as String?,
      status: RouteStatus.fromString(json['status'] as String? ?? 'draft'),
      totalStores: json['total_stores'] as int? ?? 0,
      stores: json['stores'] != null
          ? (json['stores'] as List)
              .map((s) => RoutePlanStore.fromJson(s as Map<String, dynamic>))
              .toList()
          : [],
    );
  }

  final int id;
  final String name;
  final String? description;
  final int assignedTo;
  final String? assignedUserName;
  final String frequency;
  final String startDate;
  final String? endDate;
  final RouteStatus status;
  final int totalStores;
  final List<RoutePlanStore> stores;
}

class RoutePlanStore {
  const RoutePlanStore({
    required this.id,
    required this.storeId,
    required this.visitOrder,
    this.expectedDurationMinutes,
    this.notes,
    this.store,
  });

  factory RoutePlanStore.fromJson(Map<String, dynamic> json) {
    return RoutePlanStore(
      id: json['id'] as int,
      storeId: json['store_id'] as int,
      visitOrder: json['visit_order'] as int,
      expectedDurationMinutes: json['expected_duration_minutes'] as int?,
      notes: json['notes'] as String?,
      store: json['store'] != null
          ? Store.fromJson(json['store'] as Map<String, dynamic>)
          : null,
    );
  }

  final int id;
  final int storeId;
  final int visitOrder;
  final int? expectedDurationMinutes;
  final String? notes;
  final Store? store;
}
