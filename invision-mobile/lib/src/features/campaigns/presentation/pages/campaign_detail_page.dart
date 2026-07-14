import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/enums/campaign_status.dart';
import '../../../../core/theme/app_theme.dart';
import '../providers/campaign_providers.dart';

class CampaignDetailPage extends ConsumerWidget {
  const CampaignDetailPage({super.key, required this.campaignId});

  final int campaignId;

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
  Widget build(BuildContext context, WidgetRef ref) {
    final campaignAsync = ref.watch(campaignDetailProvider(campaignId));

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Campaign Details',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: campaignAsync.when(
        data: (campaign) => ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Header gradient card
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [_statusColor(campaign.status), _statusColor(campaign.status).withOpacity(0.7)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(campaign.name,
                            style: Theme.of(context).textTheme.headlineSmall?.copyWith(color: Colors.white)),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.25),
                          borderRadius: BorderRadius.circular(100),
                        ),
                        child: Text(campaign.status.label,
                            style: const TextStyle(
                                color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text(campaign.type.label,
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.white70)),
                  if (campaign.description != null) ...[const SizedBox(height: 4),
                    Text(campaign.description!,
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.white70))],
                ],
              ),
            ),
            const SizedBox(height: 14),

            // Info card
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppColors.surfaceContainerLowest,
                borderRadius: BorderRadius.circular(16),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Details',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(color: AppColors.onSurface)),
                  const SizedBox(height: 12),
                  Divider(color: AppColors.outlineVariant.withOpacity(0.5), height: 1),
                  const SizedBox(height: 12),
                  _InfoRow('Period', '${campaign.startDate} — ${campaign.endDate}'),
                  if (campaign.budget != null)
                    _InfoRow('Budget', '\$${campaign.budget!.toStringAsFixed(2)}'),
                  if (campaign.spent != null)
                    _InfoRow('Spent', '\$${campaign.spent!.toStringAsFixed(2)}'),
                  if (campaign.budgetUtilization != null)
                    _InfoRow('Utilization', '${campaign.budgetUtilization!.toStringAsFixed(1)}%'),
                  if (campaign.creatorName != null) _InfoRow('Created By', campaign.creatorName!),
                  if (campaign.tasksCount != null) _InfoRow('Tasks', '${campaign.tasksCount}'),
                  if (campaign.entriesCount != null) _InfoRow('Entries', '${campaign.entriesCount}'),
                ],
              ),
            ),
          ],
        ),
        loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow(this.label, this.value);

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: tt.bodySmall?.copyWith(color: AppColors.onSurfaceVariant)),
          Text(value, style: tt.bodyMedium?.copyWith(color: AppColors.onSurface)),
        ],
      ),
    );
  }
}
