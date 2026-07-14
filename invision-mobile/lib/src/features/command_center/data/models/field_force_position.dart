class FieldForcePosition {
  final int id;
  final String name;
  final String role;
  final String roleLabel;
  final bool isOnline;
  final double? latitude;
  final double? longitude;
  final double? speedKmh;
  final String? lastSeen;
  final int? routeInstanceId;

  const FieldForcePosition({
    required this.id,
    required this.name,
    required this.role,
    required this.roleLabel,
    required this.isOnline,
    this.latitude,
    this.longitude,
    this.speedKmh,
    this.lastSeen,
    this.routeInstanceId,
  });

  factory FieldForcePosition.fromJson(Map<String, dynamic> json) {
    return FieldForcePosition(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      role: json['role'] as String? ?? '',
      roleLabel: json['role_label'] as String? ?? '',
      isOnline: json['is_online'] as bool? ?? false,
      latitude: (json['latitude'] as num?)?.toDouble(),
      longitude: (json['longitude'] as num?)?.toDouble(),
      speedKmh: (json['speed_kmh'] as num?)?.toDouble(),
      lastSeen: json['last_seen'] as String?,
      routeInstanceId: json['route_instance_id'] as int?,
    );
  }

  bool get hasLocation => latitude != null && longitude != null;
}
