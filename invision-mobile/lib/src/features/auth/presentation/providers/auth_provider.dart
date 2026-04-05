import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../data/models/auth_models.dart';
import '../../data/repositories/auth_repository.dart';
import '../../../../core/network/api_client.dart';

final authRepositoryProvider = Provider(
  (ref) => AuthRepository(apiClient: ref.watch(apiClientProvider)),
);

final authProvider = StateNotifierProvider<AuthNotifier, AuthState>(
  (ref) => AuthNotifier(ref.watch(authRepositoryProvider), ref),
);

class AuthNotifier extends StateNotifier<AuthState> {
  AuthNotifier(this._repository, this._ref) : super(const AuthState());

  final AuthRepository _repository;
  final Ref _ref;

  Future<void> tryRestoreSession() async {
    final token = await _repository.getSavedToken();
    if (token == null) return;

    _ref.read(apiClientProvider).setToken(token);

    try {
      final user = await _repository.me();
      state = AuthState(token: token, user: user);
    } catch (_) {
      // Token expired or invalid — stay logged out
      _ref.read(apiClientProvider).clearToken();
    }
  }

  Future<void> login({
    required String email,
    required String password,
  }) async {
    final authState = await _repository.login(
      email: email,
      password: password,
    );
    _ref.read(apiClientProvider).setToken(authState.token!);
    state = authState;
  }

  Future<void> logout() async {
    await _repository.logout();
    _ref.read(apiClientProvider).clearToken();
    state = const AuthState();
  }
}
