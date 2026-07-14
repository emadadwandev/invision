import '../../../../core/enums/store_category.dart';
import '../../../../core/enums/store_rank.dart';

class Store {
  const Store({
    required this.id,
    required this.name,
    required this.code,
    required this.category,
    required this.rank,
    required this.isActive,
    this.address,
    this.gpsLatitude,
    this.gpsLongitude,
    this.areaName,
    this.contacts = const [],
  });

  factory Store.fromJson(Map<String, dynamic> json) {
    return Store(
      id: json['id'] as int,
      name: json['name'] as String,
      code: json['code'] as String,
      category: StoreCategory.fromString(json['category'] as String? ?? ''),
      rank: StoreRank.fromString(json['rank'] as String? ?? ''),
      isActive: json['is_active'] as bool? ?? true,
      address: json['address'] as String?,
      gpsLatitude: (json['gps_latitude'] as num?)?.toDouble(),
      gpsLongitude: (json['gps_longitude'] as num?)?.toDouble(),
      areaName: json['area'] != null
          ? (json['area'] as Map<String, dynamic>)['name'] as String?
          : null,
      contacts: json['contacts'] != null
          ? (json['contacts'] as List)
              .map((c) =>
                  StoreContact.fromJson(c as Map<String, dynamic>))
              .toList()
          : [],
    );
  }

  final int id;
  final String name;
  final String code;
  final StoreCategory category;
  final StoreRank rank;
  final bool isActive;
  final String? address;
  final double? gpsLatitude;
  final double? gpsLongitude;
  final String? areaName;
  final List<StoreContact> contacts;
}

class StoreContact {
  const StoreContact({
    required this.id,
    required this.name,
    this.phone,
    this.email,
    this.position,
    this.isPrimary = false,
  });

  factory StoreContact.fromJson(Map<String, dynamic> json) {
    return StoreContact(
      id: json['id'] as int,
      name: json['name'] as String,
      phone: json['phone'] as String?,
      email: json['email'] as String?,
      position: json['position'] as String?,
      isPrimary: json['is_primary'] as bool? ?? false,
    );
  }

  final int id;
  final String name;
  final String? phone;
  final String? email;
  final String? position;
  final bool isPrimary;
}
