import '../../../../core/constants/app_constants.dart';
import '../../../../core/network/api_client.dart';

class MfaStatus {
  MfaStatus({
    required this.enabled,
    required this.confirmed,
    required this.recoveryCodesRemaining,
  });

  factory MfaStatus.fromJson(Map<String, dynamic> json) => MfaStatus(
        enabled: json['mfa_enabled'] as bool? ?? false,
        confirmed: json['mfa_confirmed'] as bool? ?? false,
        recoveryCodesRemaining:
            json['recovery_codes_remaining'] as int? ?? 0,
      );

  final bool enabled;
  final bool confirmed;
  final int recoveryCodesRemaining;
}

class MfaEnableResult {
  MfaEnableResult({
    required this.secret,
    required this.qrUri,
    required this.recoveryCodes,
  });

  factory MfaEnableResult.fromJson(Map<String, dynamic> json) =>
      MfaEnableResult(
        secret: json['secret'] as String,
        qrUri: json['provisioning_uri'] as String,
        recoveryCodes: (json['recovery_codes'] as List)
            .map((e) => e.toString())
            .toList(),
      );

  final String secret;
  final String qrUri;
  final List<String> recoveryCodes;
}

class MfaRepository {
  MfaRepository({ApiClient? apiClient})
      : _client = apiClient ?? ApiClient();
  final ApiClient _client;

  Future<MfaStatus> getStatus() async {
    final response = await _client.dio.get(ApiEndpoints.mfaStatus);
    return MfaStatus.fromJson(response.data as Map<String, dynamic>);
  }

  Future<MfaEnableResult> enable() async {
    final response = await _client.dio.post(ApiEndpoints.mfaEnable);
    return MfaEnableResult.fromJson(response.data as Map<String, dynamic>);
  }

  Future<void> confirm(String code) async {
    await _client.dio.post(ApiEndpoints.mfaConfirm, data: {'code': code});
  }

  Future<bool> verify(String code) async {
    final response =
        await _client.dio.post(ApiEndpoints.mfaVerify, data: {'code': code});
    return response.data['verified'] as bool? ?? false;
  }

  Future<void> disable(String password) async {
    await _client.dio
        .post(ApiEndpoints.mfaDisable, data: {'password': password});
  }

  Future<List<String>> regenerateRecoveryCodes() async {
    final response =
        await _client.dio.post(ApiEndpoints.mfaRecoveryCodes);
    final data = response.data['recovery_codes'] as List;
    return data.map((e) => e.toString()).toList();
  }
}
