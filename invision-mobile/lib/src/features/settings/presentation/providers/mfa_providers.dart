import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/network/api_client.dart';
import '../../data/repositories/mfa_repository.dart';

final mfaRepositoryProvider = Provider(
  (ref) => MfaRepository(apiClient: ref.watch(apiClientProvider)),
);

final mfaStatusProvider = FutureProvider.autoDispose<MfaStatus>((ref) {
  final repo = ref.watch(mfaRepositoryProvider);
  return repo.getStatus();
});
