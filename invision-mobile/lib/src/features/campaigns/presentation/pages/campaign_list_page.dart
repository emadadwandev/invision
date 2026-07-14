import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/enums/campaign_status.dart';
import '../../../../core/theme/app_theme.dart';
import '../../data/models/campaign_model.dart';
import '../providers/campaign_providers.dart';

class CampaignListPage extends ConsumerStatefulWidget {
  const CampaignListPage({super.key});

  @override
  ConsumerState<CampaignListPage> createState() => _CampaignListPageState();
}

class _CampaignListPageState extends ConsumerState<CampaignListPage> {
  final _searchController = TextEditingController();
  CampaignFilter _filter = const CampaignFilter();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _onSearch() {
    setState(() {
      _filter = _filter.copyWith(search: _searchController.text);
    });
  }

  @override
  Widget build(BuildContext context) {
    final campaignsAsync = ref.watch(campaignsProvider(_filter));
    final tt = Theme.of(context).textTheme;
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Campaigns', style: tt.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchController,
                    onSubmitted: (_) => _onSearch(),
                    decoration: const InputDecoration(
                      hintText: 'Search campaigns...',
                      prefixIcon: Icon(Icons.search_rounded, color: AppColors.outline, size: 20),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                GestureDetector(
                  onTap: _onSearch,
                  child: Container(
                    width: 48, height: 48,
                    decoration: BoxDecoration(
                      color: AppColors.primary, borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.search_rounded, color: Colors.white, size: 22),
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: campaignsAsync.when(
              data: (campaigns) => campaigns.isEmpty
                  ? Center(
                      child: Column(mainAxisSize: MainAxisSize.min, children: [
                        Container(
                          width: 64, height: 64,
                          decoration: BoxDecoration(
                            color: AppColors.surfaceContainerHigh,
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: const Icon(Icons.campaign_rounded, size: 32, color: AppColors.outline),
                        ),
                        const SizedBox(height: 12),
                        Text('No campaigns found.',
                            style: tt.bodyLarge?.copyWith(color: AppColors.onSurfaceVariant)),
                      ]),
                    )
                  : RefreshIndicator(
                      color: AppColors.primary,
                      onRefresh: () async => ref.invalidate(campaignsProvider(_filter)),
                      child: ListView.builder(
                        itemCount: campaigns.length,
                        padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
                        itemBuilder: (context, index) => _CampaignCard(campaign: campaigns[index]),
                      ),
                    ),
              loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
              error: (e, _) => Center(child: Text('Error: $e')),
            ),
          ),
        ],
      ),
    );
  }
}

class _CampaignCard extends StatelessWidget {
  const _CampaignCard({required this.campaign});

  final Campaign campaign;

  Color _statusColor(CampaignStatus status) {
    return switch (status) {
      CampaignStatus.draft => AppColors.onSurfaceVariant,
      CampaignStatus.scheduled => AppColors.primaryContainer,
      CampaignStatus.active => AppColors.secondary,
      CampaignStatus.paused => AppColors.tertiary,
      CampaignStatus.completed => AppColors.primary,
      CampaignStatus.cancelled => AppColors.error,
    };
  }

  Color _statusBg(CampaignStatus status) {
    return switch (status) {
      CampaignStatus.draft => AppColors.surfaceContainerHigh,
      CampaignStatus.scheduled => AppColors.surfaceContainerLow,
      CampaignStatus.active => AppColors.secondaryContainer,
      CampaignStatus.paused => AppColors.tertiaryContainer.withOpacity(0.3),
      CampaignStatus.completed => AppColors.surfaceContainerLow,
      CampaignStatus.cancelled => AppColors.errorContainer,
    };
  }

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;
    return GestureDetector(
      onTap: () => context.push('/campaigns/${campaign.id}'),
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surfaceContainerLowest,
          borderRadius: BorderRadius.circular(14),
          border: Border(
            left: BorderSide(color: _statusColor(campaign.status), width: 3),
          ),
        ),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(campaign.name,
                      style: tt.titleSmall?.copyWith(
                          color: AppColors.onSurface, fontWeight: FontWeight.w700)),
                  const SizedBox(height: 3),
                  Text('${campaign.type.label}  ·  ${campaign.startDate} — ${campaign.endDate}',
                      style: tt.bodySmall?.copyWith(color: AppColors.onSurfaceVariant)),
                  if (campaign.budget != null)
                    Padding(
                      padding: const EdgeInsets.only(top: 2),
                      child: Text('Budget: \$${campaign.budget!.toStringAsFixed(2)}',
                          style: tt.bodySmall?.copyWith(color: AppColors.onSurfaceVariant)),
                    ),
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: _statusBg(campaign.status),
                borderRadius: BorderRadius.circular(100),
              ),
              child: Text(
                campaign.status.label,
                style: TextStyle(
                    color: _statusColor(campaign.status),
                    fontSize: 10, fontWeight: FontWeight.w700),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
