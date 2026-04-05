import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/enums/campaign_status.dart';
import '../providers/campaign_providers.dart';

class CampaignDetailPage extends ConsumerWidget {
  const CampaignDetailPage({super.key, required this.campaignId});

  final int campaignId;

  Color _statusColor(CampaignStatus status) {
    return switch (status) {
      CampaignStatus.draft => Colors.grey,
      CampaignStatus.scheduled => Colors.blue,
      CampaignStatus.active => Colors.green,
      CampaignStatus.paused => Colors.orange,
      CampaignStatus.completed => Colors.teal,
      CampaignStatus.cancelled => Colors.red,
    };
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final campaignAsync = ref.watch(campaignDetailProvider(campaignId));

    return Scaffold(
      appBar: AppBar(title: const Text('Campaign Details')),
      body: campaignAsync.when(
        data: (campaign) => ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Header
            Row(
              children: [
                Expanded(
                  child: Text(
                    campaign.name,
                    style: Theme.of(context).textTheme.headlineSmall,
                  ),
                ),
                Chip(
                  label: Text(
                    campaign.status.label,
                    style: TextStyle(
                      color: _statusColor(campaign.status),
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  backgroundColor:
                      _statusColor(campaign.status).withValues(alpha: 0.1),
                  side: BorderSide.none,
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              campaign.type.label,
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                    color: Theme.of(context).colorScheme.primary,
                  ),
            ),
            if (campaign.description != null) ...[
              const SizedBox(height: 8),
              Text(campaign.description!),
            ],
            const SizedBox(height: 16),

            // Info card
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _InfoRow('Period',
                        '${campaign.startDate} — ${campaign.endDate}'),
                    if (campaign.budget != null)
                      _InfoRow('Budget',
                          '\$${campaign.budget!.toStringAsFixed(2)}'),
                    if (campaign.spent != null)
                      _InfoRow(
                          'Spent', '\$${campaign.spent!.toStringAsFixed(2)}'),
                    if (campaign.budgetUtilization != null)
                      _InfoRow('Utilization',
                          '${campaign.budgetUtilization!.toStringAsFixed(1)}%'),
                    if (campaign.creatorName != null)
                      _InfoRow('Created By', campaign.creatorName!),
                    if (campaign.tasksCount != null)
                      _InfoRow('Tasks', '${campaign.tasksCount}'),
                    if (campaign.entriesCount != null)
                      _InfoRow('Entries', '${campaign.entriesCount}'),
                  ],
                ),
              ),
            ),
          ],
        ),
        loading: () => const Center(child: CircularProgressIndicator()),
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
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: Theme.of(context).textTheme.bodySmall),
          Text(value, style: Theme.of(context).textTheme.bodyMedium),
        ],
      ),
    );
  }
}
