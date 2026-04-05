class AuthUser {
  const AuthUser({
    required this.id,
    required this.name,
    required this.email,
    this.role,
  });

  factory AuthUser.fromJson(Map<String, dynamic> json) {
    return AuthUser(
      id: json['id'] as int,
      name: (json['full_name'] ?? '${json['first_name'] ?? ''} ${json['last_name'] ?? ''}'.trim()) as String,
      email: json['email'] as String,
      role: json['role'] as String?,
    );
  }

  final int id;
  final String name;
  final String email;
  final String? role;
}

class AuthState {
  const AuthState({this.user, this.token});

  final AuthUser? user;
  final String? token;

  bool get isAuthenticated => token != null && user != null;
}
