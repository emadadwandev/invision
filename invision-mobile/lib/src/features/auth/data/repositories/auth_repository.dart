import 'package:shared_preferences/shared_preferences.dart';

import '../../../../core/constants/app_constants.dart';
import '../../../../core/network/api_client.dart';
import '../models/auth_models.dart';

class AuthRepository {
  AuthRepository({ApiClient? apiClient})
      : _client = apiClient ?? ApiClient();

  final ApiClient _client;

  static const _tokenKey = 'auth_token';

  Future<AuthState> login({
    required String email,
    required String password,
  }) async {
    final response = await _client.dio.post(
      ApiEndpoints.login,
      data: {
        'email': email,
        'password': password,
        'device_name': 'mobile',
      },
    );

    final data = response.data as Map<String, dynamic>;
    final token = data['token'] as String;
    final userData = data['user'] as Map<String, dynamic>;

    await _saveToken(token);

    return AuthState(
      token: token,
      user: AuthUser.fromJson(userData),
    );
  }

  Future<void> logout() async {
    try {
      await _client.dio.post(ApiEndpoints.logout);
    } finally {
      await _clearToken();
    }
  }

  Future<AuthUser> me() async {
    final response = await _client.dio.get(ApiEndpoints.me);
    final data = response.data['user'] as Map<String, dynamic>;
    return AuthUser.fromJson(data);
  }

  Future<String?> getSavedToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_tokenKey);
  }

  Future<void> _saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
  }

  Future<void> _clearToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
  }
}
