class UserActivity {
  final UserActivityInfo user;
  final UserRouteInfo? route;
  final List<UserVisitInfo> visits;
  final List<GpsTrailPoint> gpsTrail;

  const UserActivity({
    required this.user,
    this.route,
    required this.visits,
    required this.gpsTrail,
  });

  factory UserActivity.fromJson(Map<String, dynamic> json) {
    return UserActivity(
      user: UserActivityInfo.fromJson(
        json['user'] as Map<String, dynamic>? ?? {},
      ),
      route: json['route'] != null
          ? UserRouteInfo.fromJson(json['route'] as Map<String, dynamic>)
          : null,
      visits: (json['visits'] as List<dynamic>?)
              ?.map(
                (e) => UserVisitInfo.fromJson(e as Map<String, dynamic>),
              )
              .toList() ??
          [],
      gpsTrail: (json['gps_trail'] as List<dynamic>?)
              ?.map(
                (e) => GpsTrailPoint.fromJson(e as Map<String, dynamic>),
              )
              .toList() ??
          [],
    );
  }
}

class UserActivityInfo {
  final int id;
  final String name;
  final String role;

  const UserActivityInfo({
    required this.id,
    required this.name,
    required this.role,
  });

  factory UserActivityInfo.fromJson(Map<String, dynamic> json) {
    return UserActivityInfo(
      id: json['id'] as int? ?? 0,
      name: json['name'] as String? ?? '',
      role: json['role'] as String? ?? '',
    );
  }
}

class UserRouteInfo {
  final int id;
  final String? planName;
  final String status;
  final String? startedAt;

  const UserRouteInfo({
    required this.id,
    this.planName,
    required this.status,
    this.startedAt,
  });

  factory UserRouteInfo.fromJson(Map<String, dynamic> json) {
    return UserRouteInfo(
      id: json['id'] as int,
      planName: json['plan_name'] as String?,
      status: json['status'] as String? ?? '',
      startedAt: json['started_at'] as String?,
    );
  }
}

class UserVisitInfo {
  final int storeId;
  final String? storeName;
  final String status;
  final String? checkinAt;
  final String? checkoutAt;
  final int? durationMinutes;

  const UserVisitInfo({
    required this.storeId,
    this.storeName,
    required this.status,
    this.checkinAt,
    this.checkoutAt,
    this.durationMinutes,
  });

  factory UserVisitInfo.fromJson(Map<String, dynamic> json) {
    return UserVisitInfo(
      storeId: json['store_id'] as int,
      storeName: json['store_name'] as String?,
      status: json['status'] as String? ?? '',
      checkinAt: json['checkin_at'] as String?,
      checkoutAt: json['checkout_at'] as String?,
      durationMinutes: json['duration_minutes'] as int?,
    );
  }
}

class GpsTrailPoint {
  final double latitude;
  final double longitude;
  final double? speedKmh;
  final String recordedAt;

  const GpsTrailPoint({
    required this.latitude,
    required this.longitude,
    this.speedKmh,
    required this.recordedAt,
  });

  factory GpsTrailPoint.fromJson(Map<String, dynamic> json) {
    return GpsTrailPoint(
      latitude: (json['latitude'] as num).toDouble(),
      longitude: (json['longitude'] as num).toDouble(),
      speedKmh: (json['speed_kmh'] as num?)?.toDouble(),
      recordedAt: json['recorded_at'] as String? ?? '',
    );
  }
}
