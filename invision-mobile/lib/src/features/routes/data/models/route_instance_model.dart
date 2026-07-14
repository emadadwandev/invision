import '../../../../core/enums/route_status.dart';
import '../../../../core/enums/visit_status.dart';
import '../../../stores/data/models/store_model.dart';

class RouteInstance {
  const RouteInstance({
    required this.id,
    required this.routePlanId,
    required this.userId,
    required this.routeDate,
    required this.status,
    required this.totalVisits,
    required this.completedVisits,
    this.routePlanName,
    this.startedAt,
    this.completedAt,
    this.totalDistanceKm,
    this.completionPercentage = 0,
    this.visits = const [],
  });

  factory RouteInstance.fromJson(Map<String, dynamic> json) {
    return RouteInstance(
      id: json['id'] as int,
      routePlanId: json['route_plan_id'] as int,
      userId: json['user_id'] as int,
      routeDate: json['route_date'] as String,
      status: RouteStatus.fromString(json['status'] as String? ?? 'published'),
      startedAt: json['started_at'] as String?,
      completedAt: json['completed_at'] as String?,
      totalDistanceKm: (json['total_distance_km'] as num?)?.toDouble(),
      totalVisits: json['total_visits'] as int? ?? 0,
      completedVisits: json['completed_visits'] as int? ?? 0,
      completionPercentage:
          (json['completion_percentage'] as num?)?.toDouble() ?? 0,
      routePlanName: json['route_plan'] != null
          ? (json['route_plan'] as Map<String, dynamic>)['name'] as String?
          : null,
      visits: json['visits'] != null
          ? (json['visits'] as List)
              .map((v) => StoreVisit.fromJson(v as Map<String, dynamic>))
              .toList()
          : [],
    );
  }

  final int id;
  final int routePlanId;
  final int userId;
  final String routeDate;
  final RouteStatus status;
  final String? routePlanName;
  final String? startedAt;
  final String? completedAt;
  final double? totalDistanceKm;
  final int totalVisits;
  final int completedVisits;
  final double completionPercentage;
  final List<StoreVisit> visits;
}

class StoreVisit {
  const StoreVisit({
    required this.id,
    required this.routeInstanceId,
    required this.storeId,
    required this.userId,
    required this.visitOrder,
    required this.status,
    this.store,
    this.checkedInAt,
    this.checkinLatitude,
    this.checkinLongitude,
    this.checkinDistanceMeters,
    this.checkedOutAt,
    this.checkoutLatitude,
    this.checkoutLongitude,
    this.durationMinutes,
    this.notes,
    this.skipReason,
  });

  factory StoreVisit.fromJson(Map<String, dynamic> json) {
    return StoreVisit(
      id: json['id'] as int,
      routeInstanceId: json['route_instance_id'] as int,
      storeId: json['store_id'] as int,
      userId: json['user_id'] as int,
      visitOrder: json['visit_order'] as int,
      status:
          VisitStatus.fromString(json['status'] as String? ?? 'pending'),
      store: json['store'] != null
          ? Store.fromJson(json['store'] as Map<String, dynamic>)
          : null,
      checkedInAt: json['checked_in_at'] as String?,
      checkinLatitude: (json['checkin_latitude'] as num?)?.toDouble(),
      checkinLongitude: (json['checkin_longitude'] as num?)?.toDouble(),
      checkinDistanceMeters:
          (json['checkin_distance_meters'] as num?)?.toDouble(),
      checkedOutAt: json['checked_out_at'] as String?,
      checkoutLatitude: (json['checkout_latitude'] as num?)?.toDouble(),
      checkoutLongitude: (json['checkout_longitude'] as num?)?.toDouble(),
      durationMinutes: json['duration_minutes'] as int?,
      notes: json['notes'] as String?,
      skipReason: json['skip_reason'] as String?,
    );
  }

  final int id;
  final int routeInstanceId;
  final int storeId;
  final int userId;
  final int visitOrder;
  final VisitStatus status;
  final Store? store;
  final String? checkedInAt;
  final double? checkinLatitude;
  final double? checkinLongitude;
  final double? checkinDistanceMeters;
  final String? checkedOutAt;
  final double? checkoutLatitude;
  final double? checkoutLongitude;
  final int? durationMinutes;
  final String? notes;
  final String? skipReason;
}
