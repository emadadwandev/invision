import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/network/api_client.dart';
import '../../data/models/campaign_model.dart';
import '../../data/models/campaign_task_model.dart';
import '../../data/repositories/campaign_repository.dart';

final campaignRepositoryProvider = Provider(
  (ref) => CampaignRepository(apiClient: ref.watch(apiClientProvider)),
);

final campaignsProvider =
    FutureProvider.autoDispose.family<List<Campaign>, CampaignFilter>(
  (ref, filter) {
    final repo = ref.watch(campaignRepositoryProvider);
    return repo.getCampaigns(
      search: filter.search,
      status: filter.status,
      type: filter.type,
    );
  },
);

final campaignDetailProvider =
    FutureProvider.autoDispose.family<Campaign, int>(
  (ref, id) {
    final repo = ref.watch(campaignRepositoryProvider);
    return repo.getCampaign(id);
  },
);

final campaignTasksProvider =
    FutureProvider.autoDispose.family<List<CampaignTask>, int>(
  (ref, campaignId) {
    final repo = ref.watch(campaignRepositoryProvider);
    return repo.getCampaignTasks(campaignId);
  },
);

final myTasksProvider =
    FutureProvider.autoDispose.family<List<CampaignTask>, String?>(
  (ref, status) {
    final repo = ref.watch(campaignRepositoryProvider);
    return repo.getMyTasks(status: status);
  },
);

class CampaignFilter {
  const CampaignFilter({this.search, this.status, this.type});

  final String? search;
  final String? status;
  final String? type;

  CampaignFilter copyWith({String? search, String? status, String? type}) {
    return CampaignFilter(
      search: search ?? this.search,
      status: status ?? this.status,
      type: type ?? this.type,
    );
  }

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is CampaignFilter &&
          search == other.search &&
          status == other.status &&
          type == other.type;

  @override
  int get hashCode => Object.hash(search, status, type);
}
